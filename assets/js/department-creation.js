// Declaring variable 
let departmentsData;

// Fetch data from the API endpoint
fetch("/api/department_data")
  .then((response) => response.json())
  .then((data) => {
    departmentsData = data.departments;

    // Call the function that initializes the cascading dropdowns
    // after the data has been fetched
    initCascadingDropdowns();
    resetDropdowns();
  }
) 
.catch((error) => {
  console.log('Error fetching data:', error);
});


/**
 * Populates a dropdown with options based on the given data and selected id
 * @param {HTMLElement} dropdown - The dropdown element to be populated
 * @param {Array} data - The array of data to populate the dropdown with
 * @param {string} selectedId - The id of the option to be selected by default
 */
function populateDropdown(dropdown, data, selectedId) {
  // Clear the dropdown before populating it
  dropdown.innerHTML = "";

  // Create a default "Select" option and add it to the dropdown
  const defaultOption = document.createElement("option");
  defaultOption.value = "";
  defaultOption.selected = true;
  defaultOption.disabled = true;
  defaultOption.hidden = true;
  defaultOption.textContent = "Selectionner un Service";
  dropdown.appendChild(defaultOption);

  // Add each item in the data array as an option in the dropdown
  data.forEach((item) => {
    const option = document.createElement("option");
    option.value = item.id;
    option.textContent = item.name;

    // If this option should be selected, set the 'selected' attribute
    if (item.id === selectedId) {
      option.selected = true;
    }

    dropdown.appendChild(option);
  });
}


/**
 * Initializes the cascading dropdowns
 */
function initCascadingDropdowns() {
  const department = document.getElementById("department");

  if (department) {
    // Populate the department dropdown with data
    populateDropdown(department, departmentsData);

    // Reset dropdowns
    resetDropdowns();
  }
}


/**
 * Resets the dropdown to its default value
 */
function resetDropdowns() {
  const department = document.getElementById("department");

  if (department) {
    department.selectedIndex = 0;
  }
}


// Function to create a new department
document.addEventListener("turbo:load", function () {
  let createdepartmentButton = document.getElementById("create_department");

  if (createdepartmentButton) {
    createdepartmentButton.addEventListener("click", function (depcrea) {
      depcrea.preventDefault();

      // Get the value of the department name input field and trim any leading/trailing whitespace
      let departmentName = document.getElementById("department_name").value.trim();

      // Create a new XMLHttpRequest object
      let xhr = new XMLHttpRequest();
      xhr.open("POST", "/department/department_creation");
      xhr.setRequestHeader("Content-Type", "application/json");

      // Set the onload event handler for the XMLHttpRequest
      xhr.onload = function () {
        if (xhr.status >= 200 && xhr.status < 300) {
          // Parse the JSON response
          let response = JSON.parse(xhr.responseText);

          // Show the message to the user
          alert(response.message);

          // Check if the operation was successful
          if (response.success) {
            // Clear the input field after a successful submission
            document.getElementById("department_name").value = "";

            // Force a reload of the page
            location.reload();
          } else {
            // Handle failure, e.g. show error message
            console.error(response.message);
          }
        } else {
          // Handle other HTTP errors
          console.error("The request failed!");
        }
      };

      // Set the onerror event handler for the XMLHttpRequest
      xhr.onerror = function () {
        // Handle total failure of the request
        console.error("The request could not be made!");
      };

      // Send the POST request with the department name as JSON payload
      xhr.send(JSON.stringify({
        department_name: departmentName,
      }));
    });
  }
});


// Event listener to fetch department data and initialize cascading dropdowns
document.addEventListener("turbo:load", function () {
  fetch("/api/department_data")
    .then((response) => response.json())
    .then((data) => {
      departmentsData = data.departments;

      // Call the function that initializes the cascading dropdowns
      // after the data has been fetched
      initCascadingDropdowns();
      resetDropdowns();
    })
    .catch((error) => {
      console.log('Error fetching data:', error);
    });
});
