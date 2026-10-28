(() => {
    const iconClasses = {
        dashboard: 'fi-rr-home',
        students: 'fi-rr-user',
        'courses / programs': 'fi-rr-graduation-cap',
        departments: 'fi-rr-building',
        payments: 'fi-rr-credit-card',
        'payment types': 'fi-rr-tags',
        receipts: 'fi-rr-receipt',
        reports: 'fi-rr-chart-histogram',
        users: 'fi-rr-users',
        logout: 'fi-rr-sign-out-alt'
    };
    const pictographic = /\p{Extended_Pictographic}/u;
    const segmenter = new Intl.Segmenter(undefined, { granularity: 'grapheme' });

    document.querySelectorAll('.sidebar-brand .brand-text').forEach(brand => {
        if (brand.querySelector('.brand-icon')) {
            return;
        }

        const textNode = [...brand.childNodes].find(node => node.nodeType === Node.TEXT_NODE && pictographic.test(node.nodeValue ?? ''));
        const text = textNode?.nodeValue ?? '';
        const firstSegment = segmenter.segment(text.trimStart())[Symbol.iterator]().next().value?.segment ?? '';

        if (!firstSegment || !pictographic.test(firstSegment)) {
            return;
        }

        const icon = document.createElement('span');
        icon.className = 'brand-icon';
        icon.setAttribute('aria-hidden', 'true');
        icon.textContent = firstSegment;
        textNode.before(icon);
        textNode.nodeValue = text.slice(text.indexOf(firstSegment) + firstSegment.length);

        brand.querySelector('strong')?.classList.add('brand-name');
    });

    document.querySelectorAll('.sidebar-menu .nav-link > p').forEach(label => {
        const text = label.textContent.trim();
        const firstSegment = segmenter.segment(text)[Symbol.iterator]().next().value?.segment ?? '';
        const hasIcon = pictographic.test(firstSegment);
        const labelText = hasIcon ? text.slice(firstSegment.length).trim() : text;
        const iconClass = iconClasses[labelText.toLowerCase()] ?? 'fi-rr-apps';

        if (!iconClasses[labelText.toLowerCase()]) {
            console.error(`Sidebar menu item is missing a Flaticon mapping: ${labelText}`);
        }

        const link = label.closest('.nav-link');
        const icon = [...(link?.children ?? [])].find(child => child.matches('.nav-icon'))
            ?? document.createElement('i');

        icon.className = `nav-icon ${iconClass}`;
        icon.setAttribute('aria-hidden', 'true');
        icon.textContent = '';

        if (link) {
            if (!icon.parentElement) {
                label.before(icon);
            }
            link.setAttribute('aria-label', labelText);
            link.title = labelText;
        }

        if (hasIcon) {
            label.textContent = labelText;
        }
    });
})();
