(function () {
    const pageSizes = [10, 25, 50, 100];

    document.querySelectorAll("main.app-main .table-responsive > table").forEach(function (table) {
        const tbody = table.tBodies[0];
        if (!tbody) {
            return;
        }

        const rows = Array.from(tbody.rows).filter(function (row) {
            return !row.querySelector("td[colspan]");
        });
        const searchId = table.dataset.search ||
            (table.id ? table.id.replace(/Table$/, "Search") : "");
        const searchInput = searchId ? document.getElementById(searchId) : null;
        const wrapper = document.createElement("div");
        const summary = document.createElement("small");
        const footerControls = document.createElement("div");
        const pageSizeControls = document.createElement("div");
        const pageSizeLabel = document.createElement("label");
        const pageSizeSelect = document.createElement("select");
        const navigation = document.createElement("nav");
        const pageList = document.createElement("ul");
        let currentPage = 1;
        let pageSize = pageSizes[0];
        let matchingRows = rows;
        let emptyRow = null;

        if (rows.length > 0) {
            emptyRow = document.createElement("tr");
            emptyRow.style.display = "none";
            const emptyCell = document.createElement("td");
            emptyCell.colSpan = table.tHead && table.tHead.rows[0]
                ? table.tHead.rows[0].cells.length
                : 1;
            emptyCell.className = "text-center text-muted py-4";
            emptyCell.textContent = "No matching records.";
            emptyRow.appendChild(emptyCell);
            tbody.appendChild(emptyRow);
        }

        wrapper.className = "d-flex justify-content-between align-items-center flex-wrap gap-2 p-3 border-top";
        summary.className = "text-muted";
        summary.setAttribute("aria-live", "polite");
        pageSizeControls.className = "d-flex align-items-center gap-2 ms-auto";
        pageSizeLabel.className = "small text-muted mb-0";
        pageSizeLabel.textContent = "Rows per page";
        pageSizeSelect.className = "form-select form-select-sm w-auto";
        pageSizeSelect.setAttribute("aria-label", "Rows per page");
        pageSizes.forEach(function (size) {
            const option = document.createElement("option");
            option.value = String(size);
            option.textContent = String(size);
            pageSizeSelect.appendChild(option);
        });
        pageSizeControls.appendChild(pageSizeLabel);
        pageSizeControls.appendChild(pageSizeSelect);
        navigation.setAttribute("aria-label", "Table pages");
        pageList.className = "pagination pagination-sm mb-0";
        navigation.appendChild(pageList);
        footerControls.className = "d-flex align-items-center flex-wrap gap-2 ms-auto";
        footerControls.appendChild(navigation);
        wrapper.appendChild(summary);
        wrapper.appendChild(footerControls);

        const tableContainer = table.closest(".table-responsive");
        const cardHeader = table.closest(".card").querySelector(".card-header");
        cardHeader.classList.add("d-flex", "align-items-center", "flex-wrap", "gap-2");
        cardHeader.appendChild(pageSizeControls);
        tableContainer.insertAdjacentElement("afterend", wrapper);

        function addPageButton(label, page, options) {
            const item = document.createElement("li");
            const button = document.createElement("button");

            item.className = "page-item";
            if (options.disabled) {
                item.classList.add("disabled");
            }
            if (options.active) {
                item.classList.add("active");
            }

            button.type = "button";
            button.className = "page-link";
            button.textContent = label;
            button.disabled = options.disabled;
            if (options.active) {
                button.setAttribute("aria-current", "page");
            }
            if (page !== null && !options.disabled) {
                button.addEventListener("click", function () {
                    currentPage = page;
                    render();
                });
            }

            item.appendChild(button);
            pageList.appendChild(item);
        }

        function render() {
            const pageCount = Math.max(1, Math.ceil(matchingRows.length / pageSize));
            currentPage = Math.min(currentPage, pageCount);
            const startIndex = (currentPage - 1) * pageSize;
            const visibleRows = new Set(matchingRows.slice(startIndex, startIndex + pageSize));
            const firstHeader = table.tHead && table.tHead.rows[0]
                ? table.tHead.rows[0].cells[0]
                : null;
            const hasRowNumber = firstHeader && firstHeader.textContent.trim() === "#";

            rows.forEach(function (row) {
                const isVisible = visibleRows.has(row);
                row.style.display = isVisible ? "" : "none";
                if (hasRowNumber && isVisible) {
                    row.cells[0].textContent = String(matchingRows.indexOf(row) + 1);
                }
            });
            if (emptyRow) {
                emptyRow.style.display = matchingRows.length === 0 ? "" : "none";
            }

            const firstEntry = matchingRows.length === 0 ? 0 : startIndex + 1;
            const lastEntry = Math.min(startIndex + pageSize, matchingRows.length);
            summary.textContent = "Showing " + firstEntry + "–" + lastEntry +
                " of " + matchingRows.length + " entries";

            pageList.replaceChildren();
            addPageButton("Previous", currentPage - 1, { disabled: currentPage === 1 });

            const pages = new Set([1, pageCount]);
            for (let page = Math.max(1, currentPage - 2); page <= Math.min(pageCount, currentPage + 2); page++) {
                pages.add(page);
            }

            let previousPage = 0;
            Array.from(pages).sort(function (left, right) {
                return left - right;
            }).forEach(function (page) {
                if (page - previousPage > 1) {
                    addPageButton("…", null, { disabled: true });
                }
                addPageButton(String(page), page, {
                    active: page === currentPage,
                    disabled: false
                });
                previousPage = page;
            });

            addPageButton("Next", currentPage + 1, { disabled: currentPage === pageCount });
        }

        if (searchInput) {
            searchInput.addEventListener("input", function () {
                const searchTerm = searchInput.value.trim().toLocaleLowerCase();
                matchingRows = rows.filter(function (row) {
                    return row.textContent.toLocaleLowerCase().includes(searchTerm);
                });
                currentPage = 1;
                render();
            });
        }

        pageSizeSelect.addEventListener("change", function () {
            pageSize = Number(pageSizeSelect.value);
            currentPage = 1;
            render();
        });

        render();
    });
})();
