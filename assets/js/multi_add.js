document.addEventListener('DOMContentLoaded', function () {
	// Assuming you have a button with the id 'addtocart_buttonmultiproduct0'
	document.getElementById('addtocart_buttonmultiproduct0').addEventListener('click', function (event) {
		// Prevent the default form submission
		event.preventDefault();

		var productIds = []; // An array to store product IDs
		var formData = new FormData();

		// Collect product IDs and quantities
		document.querySelectorAll('.product-checkbox').forEach(function (checkbox) {
			if (checkbox.checked) {
				var productId = checkbox.value;
				var quantity = document.getElementById('quantity_' + productId).value;

				productIds.push(productId);

				// Append product ID and quantity to FormData
				formData.append('productIds[]', productId);
				formData.append('quantity_' + productId, quantity);
			}
		});

		// Make AJAX request
		fetch('index.php?option=com_ajax&plugin=vmproductmultisnapshots&format=json', {
			method: 'POST',
			body: formData
		})
			.then(response => response.json())
			.then(data => {
				// Handle the response (update UI, display messages, etc.)
				console.log(data);
			})
			.catch(error => {
				console.error('There was a problem with the fetch operation:', error);
			});
	});
});
