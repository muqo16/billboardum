// Bulk Buying Page JavaScript
document.addEventListener('DOMContentLoaded', () => {
    const selectedCities = new Set();
    const cityItems = document.querySelectorAll('.bulk-city-item');
    const totalCountEl = document.getElementById('bulk-total-count');
    const totalAmountEl = document.getElementById('bulk-total-amount');
    const citiesInput = document.getElementById('bulk-selected-cities');
    const bulkForm = document.getElementById('bulk-form');

    function calculateTotal() {
        let count = 0;
        let total = 0;
        selectedCities.forEach(slug => {
            const el = document.querySelector(`.bulk-city-item[data-slug="${slug}"]`);
            if (el) {
                count++;
                const minPrice = parseInt(el.dataset.minPrice || 10, 10);
                total += minPrice;
            }
        });

        if (totalCountEl) totalCountEl.textContent = count;
        if (totalAmountEl) totalAmountEl.textContent = total.toLocaleString('tr-TR') + '₺';
        if (citiesInput) citiesInput.value = Array.from(selectedCities).join(',');
    }

    cityItems.forEach(item => {
        item.addEventListener('click', () => {
            const slug = item.dataset.slug;
            if (selectedCities.has(slug)) {
                selectedCities.delete(slug);
                item.classList.remove('selected');
            } else {
                selectedCities.add(slug);
                item.classList.add('selected');
            }
            calculateTotal();
        });
    });

    // Quick selection buttons
    const btnSelectAll = document.getElementById('btn-select-all');
    const btnDeselectAll = document.getElementById('btn-deselect-all');

    if (btnSelectAll) {
        btnSelectAll.addEventListener('click', () => {
            cityItems.forEach(item => {
                selectedCities.add(item.dataset.slug);
                item.classList.add('selected');
            });
            calculateTotal();
        });
    }

    if (btnDeselectAll) {
        btnDeselectAll.addEventListener('click', () => {
            selectedCities.clear();
            cityItems.forEach(item => item.classList.remove('selected'));
            calculateTotal();
        });
    }

    // Bulk submit
    if (bulkForm) {
        bulkForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (selectedCities.size === 0) {
                alert('Lütfen en az bir şehir seçin.');
                return;
            }

            const btn = bulkForm.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.textContent = 'İşlem Yapılıyor...';

            const formData = new FormData(bulkForm);
            formData.append('action', 'bulk_bid');

            try {
                const res = await fetch('/api.php', { method: 'POST', body: formData });
                const data = await res.json();
                if (data.success) {
                    alert(data.message || 'Tüm şehirler başarıyla satın alındı!');
                    window.location.href = '/';
                } else {
                    alert(data.error || 'Hata oluştu.');
                }
            } catch (err) {
                alert('Hata: ' + err.message);
            } finally {
                btn.disabled = false;
                btn.textContent = 'Toplu Satın Alımı Tamamla';
            }
        });
    }
});
