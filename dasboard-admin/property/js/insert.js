document.getElementById("add-more").addEventListener("click", function () {
  const container = document.getElementById("facilities-container"); // Tempat fasilitas baru
  const newNameInput = document.getElementById("new-facility-name"); // Input untuk nama fasilitas
  const facilityName = newNameInput.value.trim(); // Ambil dan trim nilai input

  if (!facilityName) {
    alert("Masukkan nama fasilitas terlebih dahulu!");
    return;
  }

  // Membuat elemen baru untuk fasilitas
  const newFacility = document.createElement("div");
  newFacility.classList.add("form-section");
  newFacility.innerHTML = `
        <label for="${facilityName}" class="form-label">${facilityName
    .replace("_", " ")
    .toUpperCase()}</label>
        <input type="text" class="form-control" id="${facilityName}" name="features[${facilityName}]">
    `;

  // Tambahkan elemen baru ke container
  container.appendChild(newFacility);

  // Kosongkan input untuk nama fasilitas
  newNameInput.value = "";
});
