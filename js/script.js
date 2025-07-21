document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('product-search');
    const suggestionsBox = document.getElementById('suggestions');

    input.addEventListener('input', function () {
        const query = this.value;

        if (query.length < 2) {
            suggestionsBox.style.display = 'none';
            return;
        }

        fetch('../includes/search_products.php?q=' + encodeURIComponent(query))
            .then(res => res.json())
            .then(data => {
                suggestionsBox.innerHTML = '';
                if (data.length > 0) {
                    data.forEach(product => {
                        const div = document.createElement('div');
                        div.textContent = product.name;
                        div.addEventListener('click', () => {
                            input.value = product.name;
                            suggestionsBox.innerHTML = '';
                            suggestionsBox.style.display = 'none';
                        });
                        suggestionsBox.appendChild(div);
                    });
                    suggestionsBox.style.display = 'block';
                } else {
                    suggestionsBox.style.display = 'none';
                }
            });
    });

    document.addEventListener('click', function (e) {
        if (!suggestionsBox.contains(e.target) && e.target !== input) {
            suggestionsBox.style.display = 'none';
        }
    });
});