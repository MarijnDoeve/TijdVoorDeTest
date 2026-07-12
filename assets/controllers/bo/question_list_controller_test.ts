import { assertEquals } from '@std/assert';

// Stimulus's Controller base class constructor only does `this.context = context`,
// and target/value getters (listTarget, csrfValue, ...) are normally installed by
// Application.register — since we construct the controller directly (no real
// Stimulus app), they're assigned here as plain writable properties instead.
class FakeClassList {
    classes = new Set<string>();
    add(...names: string[]) {
        names.forEach((n) => this.classes.add(n));
    }
    remove(...names: string[]) {
        names.forEach((n) => this.classes.delete(n));
    }
    contains(name: string) {
        return this.classes.has(name);
    }
}

function fakeElement() {
    return {
        classList: new FakeClassList(),
        textContent: '',
        after: () => {},
        setAttribute: () => {},
    };
}

// deno-lint-ignore no-explicit-any
(globalThis as any).document = {
    createElement: () => ({
        ...fakeElement(),
        innerHTML: '',
    }),
};

const { default: QuestionListController } = await import(
    './question_list_controller.ts'
);

// deno-lint-ignore no-explicit-any
function makeController(overrides: Record<string, unknown> = {}): any {
    const controller = new QuestionListController({} as never);
    return Object.assign(controller, {
        listTarget: fakeElement(),
        itemTargets: [],
        hasStatusTarget: true,
        statusTarget: fakeElement(),
        csrfValue: 'csrf-token',
        reorderUrlValue: '/reorder',
        savedLabelValue: 'Saved',
        errorLabelValue: 'Error',
        errorHintValue: 'Refresh the page.',
        ...overrides,
    });
}

Deno.test('_setStatus renders the saving state', () => {
    const controller = makeController();
    controller._setStatus('saving');
    assertEquals(
        controller.statusTarget.classList.contains('text-bg-warning'),
        true,
    );
    assertEquals(controller.statusTarget.textContent, '…');
});

Deno.test('_setStatus renders the saved state with the configured label', () => {
    const controller = makeController();
    controller._setStatus('saved');
    assertEquals(
        controller.statusTarget.classList.contains('text-bg-success'),
        true,
    );
    assertEquals(controller.statusTarget.textContent, 'Saved');
});

Deno.test('_setStatus is a no-op when there is no status target', () => {
    const controller = makeController({ hasStatusTarget: false });
    controller._setStatus('saving');
    assertEquals(controller.statusTarget.textContent, '');
});

Deno.test('_persistOrder posts the item ordering and reports success', async () => {
    const numberEl = { textContent: '' };
    const item = {
        dataset: { questionId: '42' },
        querySelector: () => numberEl,
    };
    const controller = makeController({ itemTargets: [item] });

    let requestBody: URLSearchParams | undefined;
    globalThis.fetch = ((_url: string, init: RequestInit) => {
        requestBody = init.body as URLSearchParams;
        return Promise.resolve({ ok: true } as Response);
    }) as typeof fetch;

    await controller._persistOrder();

    assertEquals(controller._locked, false);
    assertEquals(
        controller.statusTarget.classList.contains('text-bg-success'),
        true,
    );
    assertEquals(numberEl.textContent, '1');
    assertEquals(requestBody?.get('_token'), 'csrf-token');
    assertEquals(requestBody?.get('ordering[]'), '42');
});

Deno.test('_persistOrder retries once, then locks and shows an error after repeated failure', async () => {
    const controller = makeController();
    let calls = 0;
    globalThis.fetch = (() => {
        calls++;
        return Promise.resolve({ ok: false } as Response);
    }) as typeof fetch;

    await controller._persistOrder();

    assertEquals(calls, 2);
    assertEquals(controller._locked, true);
    assertEquals(
        controller.statusTarget.classList.contains('text-bg-danger'),
        true,
    );
});

Deno.test('_persistOrder treats a network error the same as a failed response', async () => {
    const controller = makeController();
    globalThis.fetch =
        (() => Promise.reject(new Error('network down'))) as typeof fetch;

    await controller._persistOrder();

    assertEquals(controller._locked, true);
    assertEquals(
        controller.statusTarget.classList.contains('text-bg-danger'),
        true,
    );
});
