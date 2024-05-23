type FormCollectionOptions = {
    entrySelector: string;
    addLabel: string;
    maxEntries: number;
}

const defaults: FormCollectionOptions = {
    addLabel: 'Add',
    entrySelector: '> .palmtree-form-collection-entry-wrapper > .palmtree-form-collection-entry',
    maxEntries: -1
};

document.addEventListener('DOMContentLoaded', () => {
    formCollection(document.querySelectorAll('[data-palmtree-form-collection]'));

    document.body.addEventListener('palmtreeFormCollection.add', (event: CustomEvent) => {
        formCollection(event.detail.$entry.querySelectorAll('[data-palmtree-form-collection]'));
    });
});

function formCollection(widgets: NodeListOf<HTMLElement>): void {
    widgets.forEach((widget) => {
        new FormCollection(widget);
    });
}

class FormCollection {
    private options: FormCollectionOptions;
    private widget: HTMLElement;
    private addButton: HTMLButtonElement;

    constructor(widget: HTMLElement) {
        this.widget = widget;
        this.options = { ...defaults, ...JSON.parse(this.widget.dataset.palmtreeFormCollection) };

        this.addButton = this.createAddButton();

        this.widget.querySelectorAll(this.options.entrySelector).forEach((entry) => {
            this.createRemoveButton(entry);
        });
    }

    addEntry() {
        if (this.options.maxEntries > -1 && this.widget.querySelectorAll(this.options.entrySelector).length >= this.options.maxEntries) {
            return;
        }

        const index = this.widget.dataset.index || this.widget.querySelectorAll(this.options.entrySelector).length;
        const html = this.widget.dataset.prototype.replace(/__name__/g, index.toString());

        const entry = document.createElement('div');
        entry.innerHTML = html;

        this.widget.querySelector('.palmtree-form-collection-bottom').before(entry);
        this.widget.dataset.index = (parseInt(index.toString()) + 1).toString();

        this.createRemoveButton(entry);

        const event = new CustomEvent('palmtreeFormCollection.add', { detail: { entry } });

        this.widget.dispatchEvent(event);

        if (this.options.maxEntries > -1 && this.widget.querySelectorAll(this.options.entrySelector).length >= this.options.maxEntries) {
            this.addButton.disabled = true;
        }
    }

    removeEntry(entry: Element) {
        entry.parentElement.removeChild(entry);

        const event = new CustomEvent('palmtreeFormCollection.remove', { detail: { entry } });
        this.widget.dispatchEvent(event);

        if (this.options.maxEntries > -1 && this.widget.querySelectorAll(this.options.entrySelector).length < this.options.maxEntries) {
            this.addButton.disabled = false;
        }
    }

    createAddButton(): HTMLButtonElement {
        const addButton = document.createElement('button');
        addButton.type = 'button';
        addButton.className = 'btn btn-secondary btn-sm';
        addButton.textContent = this.options.addLabel;

        addButton.addEventListener('click', () => {
            this.addEntry();
        });

        const wrapper = document.createElement('section');
        wrapper.className = 'd-flex justify-content-end palmtree-form-collection-bottom';

        wrapper.append(addButton);

        this.widget.append(wrapper);

        return addButton;
    }

    createRemoveButton(entry: Element) {
        const removeButton = document.createElement('button');
        removeButton.type = 'button';
        removeButton.className = 'btn btn-close';
        removeButton.title = 'Remove';

        entry.append(removeButton);

        removeButton.addEventListener('click', () => {
            this.removeEntry(entry);
        });
    }
}

export {};
