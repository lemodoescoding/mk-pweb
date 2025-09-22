const data = {
  Laptop: ['Lenovo', 'Asus', 'Acer', 'HP', 'Dell', 'MSI', 'ROG'],
  Handphone: ['Samsung', 'Xiaomi', 'Oppo', 'Vivo', 'Realme', 'Redmi', 'iPhone'],
  'Sepeda Motor': [
    'Honda',
    'Yamaha',
    'Suzuki',
    'Kawasaki',
    'Vespa',
    'Mio',
    'Beat',
  ],
};

const produkSel = document.getElementById('produk');
const merkSel = document.getElementById('merk');

Object.keys(data).forEach((j) => {
  let option = document.createElement('option');
  option.value = j;
  option.textContent = j;

  produkSel.appendChild(option);
});

produkSel.addEventListener('change', () => {
  merkSel.innerHTML = '<option value="">--Pilih Merk--</option>';
  merkSel.disabled = true;

  let produk = produkSel.value;
  if (produk) {
    data[produk].forEach((m) => {
      let option = document.createElement('option');
      option.value = m;
      option.textContent = m;
      merkSel.appendChild(option);
    });

    merkSel.disabled = false;
  }
});
