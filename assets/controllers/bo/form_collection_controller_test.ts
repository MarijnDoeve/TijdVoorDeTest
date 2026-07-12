import { assertEquals } from '@std/assert';

// Stimulus's Controller base class constructor only does `this.context = context`,
// and target getters are normally installed by Application.register — since we
// construct the controller directly (no real Stimulus app), collectionTarget/element
// are assigned here as plain writable properties, backed by a minimal fake DOM
// container that supports the one operation these methods actually rely on:
// appendChild() moving an existing child to the end (real DOM semantics).
class FakeInput {
    value: string;
    constructor(value = '') {
        this.value = value;
    }
}

class FakeItem {
    textInput: FakeInput;
    orderingInput = new FakeInput();

    constructor(text: string) {
        this.textInput = new FakeInput(text);
    }

    querySelector<T>(selector: string): T | null {
        if (selector.includes('type="text"')) {
            return this.textInput as unknown as T;
        }
        if (selector.includes('ordering')) {
            return this.orderingInput as unknown as T;
        }
        return null;
    }
}

class FakeCollection {
    children: FakeItem[] = [];

    appendChild(item: FakeItem) {
        const idx = this.children.indexOf(item);
        if (idx !== -1) this.children.splice(idx, 1);
        this.children.push(item);
    }
}

const { default: FormCollectionController } = await import(
    './form_collection_controller.ts'
);

// deno-lint-ignore no-explicit-any
function makeController(items: FakeItem[]): any {
    const collectionTarget = new FakeCollection();
    collectionTarget.children = items;
    // `element` is a read-only getter on Controller (delegates to `this.scope.element`),
    // so the fake element is supplied via the constructor context rather than assigned.
    const controller = new FormCollectionController({
        scope: { element: { dispatchEvent: () => true } },
    } as never);
    return Object.assign(controller, { collectionTarget });
}

Deno.test("_syncOrdering writes the current index into each item's ordering input", () => {
    const items = [new FakeItem('c'), new FakeItem('a'), new FakeItem('b')];
    const controller = makeController(items);

    controller._syncOrdering();

    assertEquals(items.map((i) => i.orderingInput.value), ['0', '1', '2']);
});

Deno.test('sortAlphabetically reorders items by their text input value, case-insensitively', () => {
    const c = new FakeItem('Charlie');
    const a = new FakeItem('alice');
    const b = new FakeItem('Bob');
    const controller = makeController([c, a, b]);

    controller.sortAlphabetically();

    assertEquals(
        controller.collectionTarget.children.map((i: FakeItem) =>
            i.textInput.value
        ),
        ['alice', 'Bob', 'Charlie'],
    );
    // _syncOrdering must run after the reorder, against the new order
    assertEquals(
        controller.collectionTarget.children.map((i: FakeItem) =>
            i.orderingInput.value
        ),
        ['0', '1', '2'],
    );
});

Deno.test('randomize keeps the same set of items and resyncs ordering', () => {
    const items = [
        new FakeItem('1'),
        new FakeItem('2'),
        new FakeItem('3'),
        new FakeItem('4'),
    ];
    const controller = makeController([...items]);

    controller.randomize();

    const resultValues = controller.collectionTarget.children.map((
        i: FakeItem,
    ) => i.textInput.value);
    assertEquals(resultValues.slice().sort(), ['1', '2', '3', '4']);
    assertEquals(
        controller.collectionTarget.children.map((i: FakeItem) =>
            i.orderingInput.value
        ),
        ['0', '1', '2', '3'],
    );
});
