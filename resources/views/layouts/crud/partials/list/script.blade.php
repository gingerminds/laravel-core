<script>
    document.addEventListener('DOMContentLoaded', function () {
        const sortableHeaders = document.querySelectorAll('th.sortable');

        sortableHeaders.forEach(th => {
            th.addEventListener('click', function () {
                const sortProperty = th.dataset.sort;
                if (!sortProperty) return;

                const urlParams = new URLSearchParams(window.location.search);
                urlParams.delete('page');

                let currentSortBy = urlParams.get('sortBy');
                let currentSort = urlParams.get('sort');

                if (currentSortBy === sortProperty) {
                    currentSort = currentSort === 'desc' ? 'asc' : 'desc'; // same column: toggle direction
                } else {
                    currentSort = 'desc'; // new column: default to desc
                }

                urlParams.set('sortBy', sortProperty);
                urlParams.set('sort', currentSort);

                window.location.search = urlParams.toString();
            });
        });
    });
</script>
