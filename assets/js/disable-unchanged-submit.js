(() => {
    const formSelector = "form[data-disable-unchanged]";

    const getFormState = form => JSON.stringify(
        [...form.elements]
            .filter(field => field.name && field.type !== "hidden" && field.type !== "submit" && field.type !== "button" && !field.disabled && !field.readOnly)
            .map(field => ({
                name: field.name,
                type: field.type,
                value: field.type === "checkbox" || field.type === "radio" ? field.checked : field.value
            }))
    );

    const updateButton = form => {
        if (!form.dataset.initialState) {
            return;
        }

        const button = form.querySelector("[data-change-submit]");
        if (button) {
            button.disabled = getFormState(form) === form.dataset.initialState;
        }
    };

    document.addEventListener("show.bs.modal", event => {
        const form = event.target.querySelector(formSelector);
        if (form) {
            form.dataset.initialState = getFormState(form);
            updateButton(form);
        }
    });

    document.addEventListener("input", event => {
        const form = event.target.closest(formSelector);
        if (form) {
            updateButton(form);
        }
    });

    document.addEventListener("change", event => {
        const form = event.target.closest(formSelector);
        if (form) {
            updateButton(form);
        }
    });
})();
