const visitorForm = document.getElementById("visitorForm");
const visitorTableBody = document.querySelector("#visitorTable tbody");
let visitorCount = 0;

visitorForm.addEventListener("submit", function (e) {
  e.preventDefault(); // prevent page reload

  const name = document.getElementById("name").value;
  const email = document.getElementById("email").value;
  const phone = document.getElementById("phone").value;

  visitorCount++;

  // Add new row to table
  const row = document.createElement("tr");
  row.innerHTML = `
    <td>${visitorCount}</td>
    <td>${name}</td>
    <td>${email}</td>
    <td>${phone}</td>
  `;
  visitorTableBody.appendChild(row);

  // Reset form
  visitorForm.reset();
});
