export function setTargetVisibility(target, shouldShow = true) {
    if (shouldShow) {
        target.classList.remove("d-none");
    } else {
        target.classList.add("d-none");
        // ukrywając element automatycznie czyszczę jego wartość
        let select = target.querySelector('select');
        if (select) {
            select.value = null;
        }
    }
}
