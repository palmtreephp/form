document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll<HTMLInputElement>('.custom-file-input').forEach(function (input) {
        input.addEventListener('change', function () {
            input.parentElement.querySelector('.custom-file-label').innerHTML = input.files[0].name;
        });
    });
});
