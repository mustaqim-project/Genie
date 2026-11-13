
function openRollbackModalForStage(stageCode) {
    const modal = document.getElementById('modal-rollback-stage');
    const select = document.getElementById('rollback_target_stage');
    if (modal && select) {
        select.value = stageCode;
        modal.classList.remove('hidden');
    }
}
