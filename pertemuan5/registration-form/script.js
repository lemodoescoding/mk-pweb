const loadSuggestion = function () {
  fetch('./suggestions.json')
    .then((response) => {
      if (!response.ok) throw new Error('HTTP error: ' + response.statusText);

      return response.json();
    })
    .then((data) => {
      let nameDataList = document.getElementById('nama-mhs-datalist');
      nameDataList.innerHTML = '';

      data.students.forEach((e) => {
        let option = document.createElement('option');
        option.value = e;

        nameDataList.appendChild(option);
      });
    });
};

document.addEventListener('DOMContentLoaded', loadSuggestion());

document.getElementById('reg').addEventListener('submit', (e) => {
  let namaValue = document.getElementById('nama').value.trim();
  let nrpValue = document.getElementById('nrp').value.trim();
  let matkulValue = document.getElementById('matkul').value.trim();
  let dosenValue = document.getElementById('dosen').value.trim();

  if (!namaValue || !nrpValue || !matkulValue || !dosenValue) {
    e.preventDefault();
    if (!namaValue) {
      alert('Nama harus diisi');
    } else if (!nrpValue) {
      alert('NRP tidak boleh kosong');
    } else if (!matkulValue) {
      alert('Mata Kuliah harus diisi');
    } else {
      alert('Nama Dosen tidak boleh kosong');
    }
  }
});
