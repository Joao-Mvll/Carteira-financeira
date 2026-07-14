
(function () {
    'use strict';


    function formatMoney(value, padDecimals) {
        var v = String(value == null ? '' : value).trim();

        if (/^\d+(\.\d{1,2})?$/.test(v)) {
            v = v.replace('.', ',');
        }

        v = v.replace(/[^\d,]/g, '');
        if (v === '' || v === ',') {
            return padDecimals && v === ',' ? '0,00' : (v === ',' ? '' : v);
        }

        var firstComma = v.indexOf(',');
        if (firstComma !== -1) {
            v = v.slice(0, firstComma + 1) + v.slice(firstComma + 1).replace(/,/g, '');
        }

        var parts = v.split(',');
        var intPart = parts[0].replace(/^0+(?=\d)/, '');
        var decPart = parts.length > 1 ? parts[1].slice(0, 2) : null;

        intPart = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

        if (padDecimals) {
            if (intPart === '') intPart = '0';
            decPart = ((decPart || '') + '00').slice(0, 2);
        }

        return decPart === null ? intPart : intPart + ',' + decPart;
    }

    function initMoneyInput(input) {
        if (input.value.trim() !== '') {
            input.value = formatMoney(input.value, true);
        }

        input.addEventListener('input', function () {
            var distFromEnd = input.value.length - (input.selectionStart || 0);
            input.value = formatMoney(input.value, false);
            var pos = Math.max(0, input.value.length - distFromEnd);
            input.setSelectionRange(pos, pos);
        });

        input.addEventListener('blur', function () {
            if (input.value.trim() !== '') {
                input.value = formatMoney(input.value, true);
            }
        });
    }

    document.querySelectorAll('[data-money]').forEach(initMoneyInput);


    document.querySelectorAll('.np-quick-amount').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var form = btn.closest('form');
            var input = form ? form.querySelector('[data-money]') : null;
            if (!input) return;
            input.value = formatMoney(btn.dataset.amount, true);
            input.focus();
        });
    });


    var confirmModalEl = document.getElementById('npConfirmModal');
    var confirmModal = (confirmModalEl && typeof bootstrap !== 'undefined')
        ? new bootstrap.Modal(confirmModalEl)
        : null;
    var pendingForm = null;

    function interpolate(template, form) {
        return template.replace(/\{(\w+)\}/g, function (match, name) {
            var field = form.elements[name];
            return field && field.value !== '' ? field.value : match;
        });
    }

    function setLoading(form) {
        var btn = form.querySelector('button[type="submit"], input[type="submit"]');
        if (!btn || btn.disabled) return;
        btn.dataset.npOriginalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span> Processando…';
    }

    document.querySelectorAll('form').forEach(function (form) {
        if ((form.method || '').toLowerCase() !== 'post') return;

        form.addEventListener('submit', function (event) {
            var needsConfirm = confirmModal
                && form.hasAttribute('data-confirm')
                && form.dataset.npConfirmed !== '1'
                && form.checkValidity(); 

            if (needsConfirm) {
                event.preventDefault();

                var openModal = form.closest('.modal.show');
                if (openModal) {
                    var instance = bootstrap.Modal.getInstance(openModal);
                    if (instance) instance.hide();
                }

                document.getElementById('npConfirmModalTitle').textContent =
                    form.dataset.confirmTitle || 'Confirmar ação';
                document.getElementById('npConfirmModalBody').textContent =
                    interpolate(form.dataset.confirmMessage || 'Deseja continuar?', form);

                var ok = document.getElementById('npConfirmModalOk');
                ok.textContent = form.dataset.confirmLabel || 'Confirmar';
                ok.className = 'btn btn-' + (form.dataset.confirmVariant || 'primary');

                pendingForm = form;
                confirmModal.show();
                return;
            }

            delete form.dataset.npConfirmed;
            setLoading(form);
        });
    });

    if (confirmModal) {
        document.getElementById('npConfirmModalOk').addEventListener('click', function () {
            if (!pendingForm) return;
            var form = pendingForm;
            pendingForm = null;
            confirmModal.hide();
            form.dataset.npConfirmed = '1';
            form.requestSubmit();
        });
    }

    window.addEventListener('pageshow', function (event) {
        if (!event.persisted) return;
        document.querySelectorAll('[data-np-original-html]').forEach(function (btn) {
            btn.disabled = false;
            btn.innerHTML = btn.dataset.npOriginalHtml;
            delete btn.dataset.npOriginalHtml;
        });
    });


    if (typeof bootstrap !== 'undefined') {
        document.querySelectorAll('[data-np-tooltip]').forEach(function (el) {
            new bootstrap.Tooltip(el, {
                placement: el.dataset.npTooltip || 'top',
                trigger: 'hover',
            });
        });
    }

    var hideToggle = document.getElementById('npBalanceToggle');
    var hideables = document.querySelectorAll('[data-np-hideable]');

    if (hideToggle && hideables.length) {
        var hideIcon = hideToggle.querySelector('i');

        hideables.forEach(function (el) {
            el.dataset.npOriginal = el.textContent;
        });

        var applyHidden = function (hidden) {
            hideables.forEach(function (el) {
                el.textContent = hidden
                    ? (el.getAttribute('data-np-hideable') || 'R$ ••••••')
                    : el.dataset.npOriginal;
            });
            hideIcon.className = hidden ? 'bi bi-eye-slash' : 'bi bi-eye';
            var label = hidden ? 'Mostrar valores' : 'Ocultar valores';
            hideToggle.setAttribute('aria-label', label);
            hideToggle.setAttribute('title', label);
        };

        var valuesHidden = localStorage.getItem('np_balance_hidden') === '1';
        applyHidden(valuesHidden);

        hideToggle.addEventListener('click', function () {
            valuesHidden = !valuesHidden;
            localStorage.setItem('np_balance_hidden', valuesHidden ? '1' : '0');
            applyHidden(valuesHidden);
        });
    }


    document.querySelectorAll('[data-np-expand]').forEach(function (link) {
        link.addEventListener('click', function (event) {
            event.preventDefault();
            var target = document.getElementById(link.dataset.npExpand);
            if (!target) return;
            var expanded = target.classList.toggle('np-expanded');
            link.textContent = expanded ? 'recolher' : 'expandir';
        });
    });

    var sidebarToggle = document.getElementById('npSidebarToggle');

    if (sidebarToggle) {
        var root = document.documentElement;

        var sidebarTooltips = [];
        if (typeof bootstrap !== 'undefined') {
            document.querySelectorAll('[data-np-sidebar-tooltip]').forEach(function (el) {
                sidebarTooltips.push(new bootstrap.Tooltip(el, {
                    placement: 'right',
                    trigger: 'hover',
                }));
            });
        }

        var applySidebar = function (collapsed) {
            root.classList.toggle('np-sidebar-collapsed', collapsed);
            sidebarToggle.querySelector('i').className =
                collapsed ? 'bi bi-chevron-double-right' : 'bi bi-chevron-double-left';
            var label = collapsed ? 'Expandir menu' : 'Recolher menu';
            sidebarToggle.setAttribute('aria-label', label);
            sidebarToggle.setAttribute('title', label);
            sidebarTooltips.forEach(function (tooltip) {
                if (collapsed) {
                    tooltip.enable();
                } else {
                    tooltip.disable();
                    tooltip.hide();
                }
            });
        };

        applySidebar(root.classList.contains('np-sidebar-collapsed'));

        sidebarToggle.addEventListener('click', function () {
            var collapsed = !root.classList.contains('np-sidebar-collapsed');
            localStorage.setItem('np_sidebar_collapsed', collapsed ? '1' : '0');
            applySidebar(collapsed);
        });
    }
})();
