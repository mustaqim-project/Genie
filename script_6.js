
        // Currency Formatter with Tabular Numbers
        window.formatCurrency = function(amount, currency = 'IDR') {
            const num = Number(amount) || 0;
            return (currency === 'IDR' ? 'Rp ' : (currency + ' ')) + num.toLocaleString('id-ID');
        };

        // Universal Client-Side Table Sorting Helper (Alpine.js DOM In-Place, Zero-Reload)
        function coocaTableSort(defaultCol = null, defaultAsc = true) {
            return {
                sortCol: defaultCol,
                sortAsc: defaultAsc,
                sortBy(colIndex, type = 'string', event = null) {
                    if (this.sortCol === colIndex) {
                        this.sortAsc = !this.sortAsc;
                    } else {
                        this.sortCol = colIndex;
                        this.sortAsc = true;
                    }

                    const el = (event && (event.currentTarget || event.target)) || this.$el;
                    const table = (el && el.closest ? el.closest('table') : null) 
                        || (this.$root && (this.$root.tagName === 'TABLE' ? this.$root : this.$root.querySelector('table')))
                        || (this.$el && (this.$el.tagName === 'TABLE' ? this.$el : this.$el.querySelector('table')));
                    if (!table) return;
                    const tbody = table.querySelector('tbody');
                    if (!tbody) return;

                    const rows = Array.from(tbody.children).filter(r => 
                        r.tagName === 'TR' && 
                        !r.hasAttribute('data-empty-row') && 
                        !r.classList.contains('no-sort')
                    );
                    if (rows.length <= 1) return;

                    const multiplier = this.sortAsc ? 1 : -1;

                    const parseSortNumber = (val) => {
                        if (typeof val === 'number') return val;
                        let s = String(val).trim().replace(/[^0-9.,-]/g, '');
                        if (!s || s === '-') return 0;
                        if (s.indexOf('.') !== -1 && s.indexOf(',') !== -1) {
                            s = s.replace(/\./g, '').replace(',', '.');
                        } else if (s.indexOf('.') !== -1 && s.indexOf(',') === -1) {
                            if ((s.match(/\./g) || []).length > 1 || /\.\d{3}$/.test(s)) {
                                s = s.replace(/\./g, '');
                            }
                        }
                        const num = parseFloat(s);
                        return isNaN(num) ? 0 : num;
                    };

                    const getCellVal = (row, idx) => {
                        const cells = Array.from(row.children).filter(c => c.tagName === 'TD' || c.tagName === 'TH');
                        const cell = cells[idx];
                        if (!cell) return '';
                        const attr = cell.getAttribute('data-sort-value');
                        if (attr !== null && attr !== undefined) return attr.trim();
                        return (cell.innerText || cell.textContent || '').trim();
                    };

                    rows.sort((a, b) => {
                        const valA = getCellVal(a, colIndex);
                        const valB = getCellVal(b, colIndex);

                        if (type === 'number') {
                            return (parseSortNumber(valA) - parseSortNumber(valB)) * multiplier;
                        }
                        return String(valA).localeCompare(String(valB), undefined, { numeric: true, sensitivity: 'base' }) * multiplier;
                    });

                    rows.forEach(row => tbody.appendChild(row));
                }
            };
        }
        window.coocaTableSort = coocaTableSort;

        document.addEventListener('alpine:init', () => {
            if (window.Alpine) {
                window.Alpine.data('coocaTableSort', coocaTableSort);
            }
        });

        // Initialize Lucide Icons on DOM & Alpine Ready
        document.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) {
                window.lucide.createIcons();
            }
        });
        document.addEventListener('alpine:initialized', () => {
            if (window.lucide) {
                window.lucide.createIcons();
            }
        });
    