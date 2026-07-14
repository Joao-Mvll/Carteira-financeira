{{-- Modal genérico de confirmação: usado por qualquer form com data-confirm (ver public/js/app.js) --}}
<div class="modal fade" id="npConfirmModal" tabindex="-1" aria-labelledby="npConfirmModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="npConfirmModalTitle">Confirmar ação</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body" id="npConfirmModalBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="npConfirmModalOk">Confirmar</button>
            </div>
        </div>
    </div>
</div>
