
function navigateToWorkflowStage(posIndex) {
    let tab = 'docs';
    if (posIndex === 0) tab = 'docs';
    else if (posIndex === 1) tab = 'items';
    else if (posIndex === 2 || posIndex === 3) tab = 'ceisa';
    else tab = 'ops';

    // Switch Alpine activeTab
    const container = document.querySelector('[x-data]');
    if (container && window.Alpine) {
        Alpine.$data(container).activeTab = tab;
    } else if (container && container._x_dataStack) {
        container._x_dataStack[0].activeTab = tab;
    }

    // Smooth scroll to workspace tabs
    const target = document.getElementById('workspace-tabs') || document.getElementById('pos-assignment');
    if (target) {
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

function openViewPreviousProcessModal() {
    document.getElementById('modal-view-previous-process')?.classList.remove('hidden');
}

function selectTabAndCloseModal(tabName) {
    const container = document.querySelector('[x-data]');
    if (container && window.Alpine) {
        Alpine.$data(container).activeTab = tabName;
    } else if (container && container._x_dataStack) {
        container._x_dataStack[0].activeTab = tabName;
    }
    document.getElementById('modal-view-previous-process')?.classList.add('hidden');
    const target = document.getElementById('workspace-tabs');
    if (target) {
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}
