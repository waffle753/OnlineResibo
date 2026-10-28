(() => {
    const namePattern = /^\p{L}[\p{L} '-]*\p{L}$/u;
    const messages = {
        student_id: {
            required: "Student ID is required.",
            invalid: "Student ID must be exactly 9 digits."
        },
        first_name: {
            required: "First name is required.",
            length: "First name must be between 2 and 50 characters.",
            characters: "First name contains invalid characters."
        },
        middle_name: {
            length: "Middle name must be between 2 and 50 characters.",
            characters: "Middle name contains invalid characters."
        },
        last_name: {
            required: "Last name is required.",
            length: "Last name must be between 2 and 50 characters.",
            characters: "Last name contains invalid characters."
        },
        email: {
            invalid: "Email address must be valid.",
            length: "Email address must not exceed 254 characters."
        }
    };

    const nameError = (field, value) => {
        const trimmed = value.trim();
        const rules = messages[field.name];

        if (trimmed === "") {
            return rules.required ?? "";
        }
        if ([...trimmed].length < 2 || [...trimmed].length > 50) {
            return rules.length;
        }
        return namePattern.test(trimmed) ? "" : rules.characters;
    };

    const fieldError = field => {
        const value = field.value;

        if (field.name === "student_id") {
            if (value === "") {
                return messages.student_id.required;
            }
            return /^[0-9]{9}$/.test(value) ? "" : messages.student_id.invalid;
        }

        if (field.name === "first_name" || field.name === "middle_name" || field.name === "last_name") {
            return nameError(field, value);
        }

        if (field.name === "email") {
            const email = value.trim();
            if (email.length > 254) {
                return messages.email.length;
            }
            if (email !== "" && /\s/.test(email)) {
                return messages.email.invalid;
            }
            if (email !== "" && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                return messages.email.invalid;
            }
        }

        return "";
    };

    document.querySelectorAll('form[action="save.php"], form[action="update.php"]').forEach(form => {
        const fields = [...form.querySelectorAll('[name="student_id"], [name="first_name"], [name="middle_name"], [name="last_name"], [name="email"]')];

        const updateValidity = field => {
            field.setCustomValidity(fieldError(field));
        };

        fields.forEach(field => {
            updateValidity(field);
            field.addEventListener("input", () => updateValidity(field));
            field.addEventListener("blur", () => {
                if (field.name === "email") {
                    field.value = field.value.trim();
                } else if (field.name !== "student_id") {
                    field.value = field.value.trim();
                }
                updateValidity(field);
            });
        });

        form.addEventListener("submit", event => {
            const email = form.querySelector('[name="email"]');
            if (email) {
                email.value = email.value.trim();
            }

            fields.forEach(updateValidity);
            if (!form.reportValidity()) {
                event.preventDefault();
            }
        });
    });

    document.addEventListener("show.bs.modal", event => {
        const form = event.target.querySelector('form[action="update.php"]');
        if (!form) {
            return;
        }

        form.querySelectorAll('[name="student_id"], [name="first_name"], [name="middle_name"], [name="last_name"], [name="email"]')
            .forEach(field => {
                if (field.name === "email" || field.name === "first_name" || field.name === "middle_name" || field.name === "last_name") {
                    field.value = field.value.trim();
                }
                field.setCustomValidity(fieldError(field));
            });
    });
})();
