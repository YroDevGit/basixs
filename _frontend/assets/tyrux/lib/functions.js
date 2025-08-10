export class DOMclass {
    set_html(selector, strhtml) {
        let elements = [];

        if (selector.charAt(0) === "#") {
            const element = document.getElementById(selector.substring(1));
            if (element) {
                elements.push(element);
            }
        } else if (selector.charAt(0) === ".") {
            elements = Array.from(document.querySelectorAll(selector));
        } else {
            const element = document.getElementById(selector);
            if (element) {
                elements.push(element);
            }
        }
        if (elements.length > 0) {
            elements.forEach(element => {
                element.innerHTML = strhtml;
            });
        } else {
            console.warn(`No elements found for selector: "${selector}"`);
        }
    }

    add_html() {
        let elements = [];

        if (selector.charAt(0) === "#") {
            const element = document.getElementById(selector.substring(1));
            if (element) {
                elements.push(element);
            }
        } else if (selector.charAt(0) === ".") {
            elements = Array.from(document.querySelectorAll(selector));
        } else {
            const element = document.getElementById(selector);
            if (element) {
                elements.push(element);
            }
        }

        if (elements.length > 0) {
            elements.forEach(element => {
                element.insertAdjacentHTML('beforeend', strhtml);
            });
        } else {
            console.warn(`No elements found for selector: "${selector}"`);
        }
    }
}