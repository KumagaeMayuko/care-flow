window.onload = function () {
    const categoryButtons = document.querySelectorAll('.category_create_btn');
    categoryButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            const ctg_input_zone = this.parentNode.querySelector('.ctg_input_zone');
            // const ctg_input_zone = this.nextElementSibling;
            ctg_input_zone.style.display = ctg_input_zone.style.display === 'block' ? 'none' : 'block';
        });
    });
};

