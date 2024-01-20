document.addEventListener('DOMContentLoaded', function () {

	document.getElementById('addtocart_buttonmultiproduct0').addEventListener('click', function (event) {
		// Prevent the default form submission
		event.preventDefault();

		var productIds = [];
		var formData = new FormData();

		var productForms = document.querySelectorAll('.product.js-recalculate');
		productForms.forEach(function (form) {
			var quantityInput = form.querySelector('.quantity-input');
			var productIdInput = form.querySelector('input[name="virtuemart_product_id[]"]');

			var quantity = quantityInput.value;
			var productId = productIdInput.value;


			if (quantity > 0) {
				console.log(productId);
				productIds.push(productId, quantity);
				formData.append('productIds[]', productId);
				formData.append('quantity_' + productId, quantity);
			}
		});

		fetch('index.php?option=com_ajax&group=content&plugin=addMultiProducts&format=json', {
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
