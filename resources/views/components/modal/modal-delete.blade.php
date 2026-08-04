<div class="modal fade" id="removeModal{{ $idSuffix ?? '' }}" data-suffix="{{ $idSuffix ?? '' }}" tabindex="-1" aria-labelledby="removeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="removeModalLabel"
                    data-title-delete="@lang('gingerminds-core::translation.modal.title_delete')">
                    @lang('gingerminds-core::translation.modal.title_default')
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4 py-5 text-center">
                <form method="POST" action="#" autocomplete="off" class="needs-validation" id="removeForm" novalidate>
                    @csrf
                    @method('DELETE')

                    <div class="modal-delete-icon bg-danger text-danger bg-opacity-10 mb-4 mx-auto">
                        <i class="bi bi-trash"></i>
                    </div>
                    <p id="remove-confirm" class="text-muted fs-16 mb-4"
                       data-template="@lang('gingerminds-core::translation.action.remove_confirm', ['name' => ':name'])">
                    </p>
                    <div class="hstack gap-2 justify-content-center mb-0">
                        <button type="button" class="btn btn-danger" id="remove-item">@lang('gingerminds-core::translation.action.remove')</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('gingerminds-core::translation.action.cancel')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const suffix = @json($idSuffix ?? '');
            const removeModalEl = document.getElementById('removeModal' + suffix);
            if (!removeModalEl) return;

            const titleEl = removeModalEl.querySelector('#removeModalLabel');

            document.querySelectorAll('.js-remove-item').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const name = btn.dataset.removeName || '';
                    const destroyUrl = btn.dataset.destroyUrl || '#';

                    const mode   = btn.dataset.mode || 'delete'; // create | edit
                    const gender = btn.dataset.gender || 'm';  // m | f
                    const model  = btn.dataset.model  || '';   // ex: "rôle", "catégorie"
                    const article = gender === 'f' ? 'une' : 'un';

                    // Template key is built from data-title-{mode}, e.g. data-title-delete
                    const template = titleEl.dataset[`title${mode.charAt(0).toUpperCase() + mode.slice(1)}`];
                    if (template) {
                        titleEl.textContent = template
                            .replace('{article}', article)
                            .replace('{model}', model);
                    }

                    const confirmEl = removeModalEl.querySelector('#remove-confirm');
                    if (confirmEl) {
                        const template = confirmEl.dataset.template || '';
                        confirmEl.textContent = template.replace(':name', name);
                    }

                    const form = removeModalEl.querySelector('#removeForm');
                    if (form) form.setAttribute('action', destroyUrl);
                });
            });

            const removeBtn = removeModalEl.querySelector('#remove-item');
            if (removeBtn) {
                removeBtn.addEventListener('click', function () {
                    const form = removeModalEl.querySelector('#removeForm');
                    if (form) form.submit();
                });
            }
        });
    </script>
@endpush
