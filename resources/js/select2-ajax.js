/**
 * Wires select2's `ajax` remote-search mode onto any `<select class="select2-search" data-ajax-url="...">`
 * rendered by the `select` form input component (`ajaxUrl`/`maxSelection` props).
 *
 * @param {JQueryStatic} $
 * @param {(el: Element) => void} resetPlaceholder - called after init/select/unselect to reset the search placeholder
 */
export default function initAjaxSelect2($, resetPlaceholder) {
    $('.select2-search[data-ajax-url]').each(function () {
        const $el = $(this);
        const maxSelection = parseInt($el.data('max-selection'), 10) || 0;

        $el.select2({
            ajax: {
                url: $el.data('ajax-url'),
                dataType: 'json',
                delay: 250,
                data: (params) => ({ q: params.term }),
                processResults: (data) => ({ results: data.results }),
            },
            minimumInputLength: 2,
            maximumSelectionLength: maxSelection,
            placeholder: $el.data('placeholder') || 'Add an option',
        }).on('select2:select select2:unselect', () => resetPlaceholder(this));
        resetPlaceholder(this);
    });
}
