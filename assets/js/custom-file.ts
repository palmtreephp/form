document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll<HTMLInputElement>('.custom-file-input').forEach(function (input) {
        input.addEventListener('change', function () {
            const label = input.parentElement?.querySelector('.custom-file-label');
            if (label && input.files) {
                label.innerHTML = input.files[0].name;
            }
        });
    });
});
