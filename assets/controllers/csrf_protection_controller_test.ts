import { assertEquals, assertMatch } from '@std/assert';

// The module under test registers `document.addEventListener(...)` calls at
// import time and reads `window.location`/`window.crypto`, so a minimal
// `document`/`window` stub must exist in the global scope *before* the module
// is imported. Deno has no browser DOM, and the actual surface these
// functions touch on a form/input is narrow (getAttribute/setAttribute/value/
// dispatchEvent), so hand-built fakes are used instead of a full DOM polyfill.
let cookieJar = '';
// deno-lint-ignore no-explicit-any
(globalThis as any).document = {
    addEventListener: () => {},
    get cookie() {
        return cookieJar;
    },
    set cookie(value: string) {
        cookieJar += (cookieJar ? '; ' : '') + value;
    },
};
// deno-lint-ignore no-explicit-any
(globalThis as any).window = {
    location: { protocol: 'http:' },
    crypto: globalThis.crypto,
};

const { generateCsrfHeaders, generateCsrfToken, removeCsrfToken } =
    await import(
        './csrf_protection_controller.ts'
    );

function fakeField(attributes: Record<string, string> = {}, value = '') {
    const store = new Map(Object.entries(attributes));
    return {
        getAttribute: (name: string) => store.get(name) ?? null,
        setAttribute: (name: string, val: string) => {
            store.set(name, val);
        },
        value,
        defaultValue: value,
        dispatchEvent: () => true,
    };
}

// deno-lint-ignore no-explicit-any
function fakeForm(field: ReturnType<typeof fakeField> | null): any {
    return { querySelector: () => field };
}

Deno.test('generateCsrfToken does nothing when the form has no csrf field', () => {
    cookieJar = '';
    generateCsrfToken(fakeForm(null));
    assertEquals(cookieJar, '');
});

Deno.test('generateCsrfToken generates a token and cookie on first use', () => {
    cookieJar = '';
    const field = fakeField({}, 'my_field_name');

    generateCsrfToken(fakeForm(field));

    assertEquals(
        field.getAttribute('data-csrf-protection-cookie-value'),
        'my_field_name',
    );
    assertMatch(field.defaultValue, /^[-_/+a-zA-Z0-9]{24,}$/);
    assertMatch(cookieJar, /my_field_name_[-_/+a-zA-Z0-9]{24,}=my_field_name/);
});

Deno.test('generateCsrfToken keeps an existing valid token/cookie pair unchanged', () => {
    cookieJar = '';
    const token = 'a'.repeat(24);
    const field = fakeField({
        'data-csrf-protection-cookie-value': 'existing_name',
    }, token);

    generateCsrfToken(fakeForm(field));

    assertEquals(field.value, token);
    assertMatch(cookieJar, new RegExp(`existing_name_${token}=existing_name`));
});

Deno.test('generateCsrfHeaders returns empty headers without a csrf field', () => {
    assertEquals(generateCsrfHeaders(fakeForm(null)), {});
});

Deno.test('generateCsrfHeaders returns the cookie-name/token header when both are valid', () => {
    const token = 'b'.repeat(24);
    const field = fakeField({
        'data-csrf-protection-cookie-value': 'field_name',
    }, token);

    assertEquals(generateCsrfHeaders(fakeForm(field)), { field_name: token });
});

Deno.test('generateCsrfHeaders omits the header when the token is too short', () => {
    const field = fakeField({
        'data-csrf-protection-cookie-value': 'field_name',
    }, 'too-short');

    assertEquals(generateCsrfHeaders(fakeForm(field)), {});
});

Deno.test('removeCsrfToken expires the cookie for a valid token/cookie pair', () => {
    cookieJar = '';
    const token = 'c'.repeat(24);
    const field = fakeField({
        'data-csrf-protection-cookie-value': 'field_name',
    }, token);

    removeCsrfToken(fakeForm(field));

    assertMatch(cookieJar, new RegExp(`field_name_${token}=0`));
});

Deno.test('removeCsrfToken does nothing without a csrf field', () => {
    cookieJar = '';
    removeCsrfToken(fakeForm(null));
    assertEquals(cookieJar, '');
});
