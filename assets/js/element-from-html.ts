export function elementFromHtml<TElement extends Element>(html: string): TElement {
    const template = document.createElement("template");
    template.innerHTML = html.trim();

    return template.content.firstChild as TElement;
}
