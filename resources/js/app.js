const $ = window.jQuery;
window.$ = $;
import Sortable from 'sortablejs';
window.Sortable = Sortable;
import 'bootstrap';
import initSelect2 from 'select2';
initSelect2(window, $);

function resetMultipleSelectPlaceholder(el) {
    $(el).next('.select2-container')
         .find('.select2-search__field')
         .attr('placeholder', 'Add an option');
}

document.addEventListener('click', function (e) {
    const toggle = e.target.closest('#password-addon');
    if (!toggle) return;

    const input = toggle.closest('.input-group')?.querySelector('input[type="password"], input[type="text"]');
    if (!input) return;

    const icon = toggle.querySelector('i');
    const showPassword = input.type === 'password';

    input.type = showPassword ? 'text' : 'password';

    if (icon) {
        icon.classList.toggle('bi-eye', !showPassword);
        icon.classList.toggle('bi-eye-slash', showPassword);
    }
});

document.addEventListener('DOMContentLoaded', function () {
    $('.select2:not([multiple])').select2();

    $('.select2[multiple]').each(function () {
        $(this).select2({ placeholder: 'Add an option' })
               .on('select2:select select2:unselect', () => resetMultipleSelectPlaceholder(this));
        resetMultipleSelectPlaceholder(this);
    });

    const sidebar = document.getElementById('mainSidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');

    if (sidebar && sidebarToggle) {
        if (localStorage.getItem('sidebarCollapsed') === '1') {
            sidebar.classList.add('collapsed');
        }

        sidebarToggle.addEventListener('click', function () {
            const collapsed = sidebar.classList.toggle('collapsed');
            localStorage.setItem('sidebarCollapsed', collapsed ? '1' : '0');
        });
    }
});
