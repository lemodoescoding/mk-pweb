let postalCodes = {
  'Jawa Timur': {
    Surabaya: {
      Tegalsari: '60262',
      Wonokromo: '60243',
      Keputih: '60111',
      'Kejawan Putih Tambak': '60112',
      'Gebang Putih': '60117',
    },
    Malang: {
      Klojen: '65111',
      Lowokwaru: '65141',
    },
    Sidoarjo: {
      Buduran: '61252',
      Candi: '61271',
    },
    Lamongan: {
      Paciran: '62264',
      Lamongan: '62212',
    },
  },
  'Jawa Barat': {
    Bandung: {
      Coblong: '40132',
      Lengkong: '40261',
    },
    Bekasi: {
      'Bekasi Barat': '17134',
      'Bekasi Timur': '17111',
    },
    Bogor: {
      'Bogor Tengah': '16121',
      'Bogor Selatan': '16133',
    },
  },
  'Jawa Tengah': {
    Semarang: {
      Candisari: '50252',
      Tembalang: '50275',
    },
    Solo: {
      Laweyan: '57148',
      Banjarsari: '57138',
    },
    Magelang: {
      'Magelang Tengah': '56121',
      'Magelang Selatan': '56123',
    },
  },
  'DKI Jakarta': {
    'Jakarta Selatan': {
      'Kebayoran Baru': '12120',
      'Pasar Minggu': '12520',
    },
    'Jakarta Barat': {
      Cengkareng: '11730',
      'Grogol Petamburan': '11450',
    },
    'Jakarta Timur': {
      Matraman: '13110',
      'Duren Sawit': '13440',
    },
  },
  'DI Yogyakarta': {
    'Kota Yogyakarta': {
      Gondokusuman: '55224',
      Umbulharjo: '55161',
    },
    Sleman: {
      Depok: '55281',
      Ngaglik: '55581',
    },
    Bantul: {
      Banguntapan: '55191',
      Sewon: '55186',
    },
  },
};

const provinsiSel = document.getElementById('provinsi');
const kabkotaSel = document.getElementById('kabkota');
const kecamatanSel = document.getElementById('kecamatan');

Object.keys(postalCodes).forEach((prov) => {
  let option = document.createElement('option');
  option.value = prov;
  option.textContent = prov;

  provinsiSel.append(option);
});

provinsiSel.addEventListener('change', () => {
  kabkotaSel.innerHTML = '<option value="">-- Pilih Kab/Kota --</option>';
  kecamatanSel.innerHTML = '<option value="">-- Pilih Kecamatan --</option>';
  kabkotaSel.disabled = true;
  kecamatanSel.disabled = true;

  let prov = provinsiSel.value;

  if (prov) {
    Object.keys(postalCodes[prov]).forEach((kab) => {
      let option = document.createElement('option');
      option.value = kab;
      option.textContent = kab;

      kabkotaSel.append(option);

      console.log(kab);
    });

    kabkotaSel.disabled = false;
  }
});

kabkotaSel.addEventListener('change', () => {
  kecamatanSel.innerHTML = '<option value="">-- Pilih Kecamatan --</option>';
  kecamatanSel.disabled = true;

  let prov = provinsiSel.value;
  let kab = kabkotaSel.value;

  if (kab) {
    Object.keys(postalCodes[prov][kab]).forEach((kec) => {
      let option = document.createElement('option');
      option.value = kec;
      option.textContent = kec;

      kecamatanSel.append(option);

      console.log(kec);
    });

    kecamatanSel.disabled = false;
  }
});

const result = document.getElementById('result');

document.getElementById('kodepos-reg').addEventListener('submit', (e) => {
  e.preventDefault();

  let prov = provinsiSel.value;
  let kab = kabkotaSel.value;
  let kec = kecamatanSel.value;

  if (prov && kab && kec) {
    let kodepos = postalCodes[prov][kab][kec];
    result.style.display = 'block';
    result.textContent = `Kode Pos ${prov}, ${kab}, ${kec} : ${kodepos}`;
  } else {
    result.style.display = 'block';
    result.textContent = 'Lengkapi semua pilihan terlebih dahulu';
  }
});
