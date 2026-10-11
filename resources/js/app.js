// Tema claro/oscuro: el script inline del <head> aplica la clase antes de pintar; acá solo el botón.
document.querySelectorAll('[data-theme-toggle]').forEach((btn) => {
    btn.addEventListener('click', () => {
        const dark = document.documentElement.classList.toggle('dark');
        try {
            localStorage.setItem('theme', dark ? 'dark' : 'light');
        } catch {}
    });
});

// Menú mobile
const menu = document.getElementById('mobile-menu');
document.querySelectorAll('[data-menu-toggle]').forEach((btn) => {
    btn.addEventListener('click', () => {
        const open = menu.classList.toggle('hidden') === false;
        document.querySelectorAll('[data-menu-toggle]').forEach((b) => b.setAttribute('aria-expanded', String(open)));
        document.body.classList.toggle('overflow-hidden', open);
    });
});

// Evita doble envío de formularios
document.querySelectorAll('form[data-once]').forEach((form) => {
    form.addEventListener('submit', () => {
        form.querySelectorAll('button[type=submit]').forEach((b) => {
            b.disabled = true;
            b.dataset.label && (b.textContent = b.dataset.label);
        });
    });
});

// Confirmación en botones de borrar (panel)
document.querySelectorAll('form[data-confirm]').forEach((form) => {
    form.addEventListener('submit', (e) => {
        if (!confirm(form.dataset.confirm)) e.preventDefault();
    });
});

// Carrito: cambiar cantidad envía el formulario
document.querySelectorAll('[data-autosubmit]').forEach((el) => {
    el.addEventListener('change', () => el.form.requestSubmit());
});

// Hosting: pestañas DirectAdmin / cPanel
document.querySelectorAll('[data-tabs]').forEach((tabs) => {
    const buttons = tabs.querySelectorAll('[role=tab]');
    buttons.forEach((btn) => {
        btn.addEventListener('click', () => {
            buttons.forEach((b) => {
                const active = b === btn;
                b.setAttribute('aria-selected', String(active));
                document.getElementById(b.getAttribute('aria-controls')).hidden = !active;
            });
        });
    });
});

// Portfolio: filtrar certificaciones por emisor
document.querySelectorAll('[data-cert-filter]').forEach((box) => {
    const buttons = box.querySelectorAll('[data-issuer]');
    buttons.forEach((btn) => {
        btn.addEventListener('click', () => {
            buttons.forEach((b) => b.setAttribute('aria-pressed', String(b === btn)));
            box.querySelectorAll('[data-cert-issuer]').forEach((li) => {
                li.hidden = btn.dataset.issuer !== '' && li.dataset.certIssuer !== btn.dataset.issuer;
            });
            // Años sin certificados del emisor elegido se ocultan; el contador se actualiza.
            box.querySelectorAll('[data-cert-year]').forEach((year) => {
                const visible = year.querySelectorAll('[data-cert-issuer]:not([hidden])').length;
                year.hidden = visible === 0;
                const count = year.querySelector('[data-year-count]');
                if (count) count.textContent = count.textContent.replace(/^\d+/, visible);
            });
            if (btn.dataset.issuer !== '') box.querySelectorAll('[data-cert-year]:not([hidden])').forEach((y) => (y.open = true));
        });
    });
});

// Checkout: mostrar campos de envío y cotizar por código postal.
// La cotización es solo informativa: el servidor vuelve a calcular todo al confirmar.
document.querySelectorAll('[data-checkout]').forEach((form) => {
    const fields = form.querySelector('[data-shipping-fields]');
    const postal = form.querySelector('[data-postal]');
    const result = form.querySelector('[data-quote-result]');
    const shippingOut = form.querySelector('[data-summary-shipping]');
    const totalOut = form.querySelector('[data-summary-total]');
    const initialTotal = totalOut.textContent;
    let timer;
    let lastQuote = null;

    const isShipping = () => form.querySelector('[data-delivery]:checked')?.value === 'shipping';

    const render = () => {
        const shipping = isShipping();
        fields.hidden = !shipping;
        fields.querySelectorAll('input, select').forEach((i) => (i.required = shipping && !('optional' in i.dataset)));
        if (!shipping) {
            shippingOut.textContent = form.dataset.freeLabel;
            totalOut.textContent = initialTotal;
        } else if (lastQuote?.ok) {
            shippingOut.textContent = lastQuote.cost_label;
            totalOut.textContent = lastQuote.total_label;
        } else {
            shippingOut.textContent = '—';
            totalOut.textContent = initialTotal;
        }
    };

    const quote = async () => {
        const cp = postal.value.trim();
        const val = (n) => form.elements[n]?.value.trim() || '';
        const params = new URLSearchParams();
        if (/\d{4}/.test(cp)) params.set('cp', cp);
        else if (val('city') && val('province')) ['address', 'city', 'province'].forEach((n) => params.set(n, val(n)));
        if (![...params.keys()].length) {
            lastQuote = null;
            result.textContent = '';
            return render();
        }
        try {
            const res = await fetch(`${form.dataset.quoteUrl}?${params}`, { headers: { Accept: 'application/json' } });
            lastQuote = await res.json();
            result.className = 'mt-1.5 text-sm ' + (lastQuote.ok ? 'text-emerald-700 dark:text-emerald-400' : 'text-red-600');
            result.textContent = lastQuote.ok
                ? `${!cp && lastQuote.postal_code ? 'CP ' + lastQuote.postal_code + ' · ' : ''}${lastQuote.zone}: ${lastQuote.cost_label}${lastQuote.eta ? ' · ' + lastQuote.eta : ''}${lastQuote.free_from ? ' · ' + lastQuote.free_from : ''}`
                : lastQuote.message;
        } catch {
            lastQuote = null;
            result.textContent = '';
        }
        render();
    };

    form.querySelectorAll('[data-delivery]').forEach((r) => r.addEventListener('change', render));
    form.querySelectorAll('[data-addr]').forEach((el) => el.addEventListener('change', () => !/\d{4}/.test(postal.value) && quote()));
    postal?.addEventListener('input', () => {
        clearTimeout(timer);
        timer = setTimeout(quote, 400);
    });
    render();
    if (postal?.value) quote();
});
