<div class="modal fade" id="{{ $id ?? 'confirmModal' }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ $title ?? __('gingerminds-core::translation.confirm.title') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4 py-5 text-center">
                <div class="avatar-sm mb-4 mx-auto">
                    <div class="avatar-title bg-primary text-primary bg-opacity-10 fs-24 rounded-3">
                        <i class="bi bi-question-circle"></i>
                    </div>
                </div>
                <p class="text-muted fs-16 mb-4">{{ $message ?? __('gingerminds-core::translation.confirm.message') }}</p>
                <div class="hstack gap-2 justify-content-center mb-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        @lang('gingerminds-core::translation.action.cancel')
                    </button>
                    <a href="#" class="btn btn-primary" id="{{ $id ?? 'confirmModal' }}-confirm-btn">
                        @lang('gingerminds-core::translation.action.confirm')
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.querySelector('.crud-form');
            let isDirty = false;

            form.addEventListener('change', () => isDirty = true);
            form.addEventListener('input', () => isDirty = true);

            form.addEventListener('submit', () => isDirty = false);

            document.querySelectorAll('.js-confirm-action').forEach(btn => {
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    const url = this.dataset.confirmUrl;

                    if (isDirty) {
                        const confirmBtn = document.getElementById('confirmModal-confirm-btn');
                        confirmBtn.href = url;
                        bootstrap.Modal.getOrCreateInstance(document.getElementById('confirmModal')).show();
                    } else {
                        window.location.href = url;
                    }
                });
            });

            const modalEl = document.getElementById('{{ $id ?? 'confirmModal' }}');
            if (!modalEl) return;

            const confirmBtn = modalEl.querySelector('#{{ $id ?? 'confirmModal' }}-confirm-btn');

            modalEl.addEventListener('show.bs.modal', function (e) {
                const trigger = e.relatedTarget;
                if (trigger && trigger.dataset.confirmUrl) {
                    confirmBtn.href = trigger.dataset.confirmUrl;
                }
            });
        });
    </script>
@endpush
