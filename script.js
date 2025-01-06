const submit_btn = document.getElementById("submit");
const data_table = document.getElementById("data");
const user = document.getElementById("user");

submit_btn.onclick = function (e) {
  e.preventDefault(); // Prevent default form submission

  // Get the selected user ID
  const userId = user.value;
  const userName = user.options[user.options.selectedIndex].textContent;

  // Make an AJAX request to data.php
  const xhr = new XMLHttpRequest();
  xhr.open("GET", `data.php?user=${userId}&name=${userName}`, true); // Use GET with user ID as a parameter
  xhr.onload = function () {
    if (xhr.status === 200) {
      data_table.innerHTML = xhr.responseText; // Update the data table content
      data_table.style.display = "block"; // Show the data table
    } else {
      console.error("Error fetching data:", xhr.statusText);
      alert("Something went wrong, please refresh the page and try again.");
    }
  };
  xhr.send();
};
