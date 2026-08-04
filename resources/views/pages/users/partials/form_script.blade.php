<script>
    (function () {
        const select = document.getElementById('contributor_id');
        const box = document.getElementById('contributor-fields');
        const fFirst = document.getElementById('contributor_firstname');
        const fLast = document.getElementById('contributor_lastname');
        const fTrig = document.getElementById('contributor_trigram');
        const fCiv = document.getElementById('contributor_civility');

        function syncVisibility() {
            if (!select) return;
            const hasSelection = !!select.value;
            if (box) box.style.display = hasSelection ? '' : 'none';

            if (hasSelection) {
                const opt = select.options[select.selectedIndex];
                if (opt) {
                    if (fFirst && !fFirst.value) fFirst.value = opt.getAttribute('data-firstname') || '';
                    if (fLast && !fLast.value) fLast.value = opt.getAttribute('data-lastname') || '';
                    if (fTrig && !fTrig.value) fTrig.value = opt.getAttribute('data-trigram') || '';
                    if (fCiv && !fCiv.value) fCiv.value = opt.getAttribute('data-civility') || '';
                }
            }
        }

        if (select) {
            select.addEventListener('change', function () {
                const value = select.value;
                const opt = select.options[select.selectedIndex];
                if (value === '__new__') {
                    // '__new__' means "create a new contributor": clear fields for manual entry
                    if (fFirst) fFirst.value = '';
                    if (fLast) fLast.value = '';
                    if (fTrig) fTrig.value = '';
                    if (fCiv) fCiv.value = '';
                } else if (opt) {
                    if (fFirst) fFirst.value = opt.getAttribute('data-firstname') || '';
                    if (fLast) fLast.value = opt.getAttribute('data-lastname') || '';
                    if (fTrig) fTrig.value = opt.getAttribute('data-trigram') || '';
                    if (fCiv) fCiv.value = opt.getAttribute('data-civility') || '';
                }
                syncVisibility();
            });
        }

        syncVisibility();
    })();
</script>
