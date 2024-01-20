document.addEventListener('DOMContentLoaded', function () {

	document.getElementById('addtocart_buttonmultiproduct0').addEventListener('click', function (event) {
		// Prevent the default form submission
		event.preventDefault();
		var loaderWrapper = document.getElementById("loader-wrapper");
		loaderWrapper.style.display = "flex";
		var productIds = [];
		var formData = new FormData();
		var noticeElement = document.querySelector('.flance-notice');
		var productForms = document.querySelectorAll('.product.js-recalculate');
		productForms.forEach(function (form) {
			var quantityInput = form.querySelector('.quantity-input');
			var productIdInput = form.querySelector('input[name="virtuemart_product_id[]"]');

			var quantity = quantityInput.value;
			var productId = productIdInput.value;


			if (quantity > 0) {

				productIds.push(productId, quantity);
				formData.append('productIds[]', productId);
				formData.append('quantity[]', quantity);
			}
		});
		noticeElement.style.display = 'none';
		fetch('index.php?option=com_ajax&group=content&plugin=addMultiProducts&format=json', {
			method: 'POST',
			body: formData
		})
			.then(response => response.json())
			.then(data => {
				if (data.success === true) {
					loaderWrapper.style.display = "none";
					noticeElement.textContent = data.message;
					noticeElement.style.display = 'block';
					window.location.href = data.cart_url;
				} else {
					loaderWrapper.style.display = "none";
					noticeElement.textContent = data.message;
					noticeElement.style.display = 'block';
				}
			})
			.catch(error => {
				console.error('There was a problem with the fetch operation:', error);
			});
	});
});



window.addEventListener('load', function () {
    	var loaderWrapper = document.getElementById("loader-wrapper");
	loaderWrapper.style.display = "none";
});
