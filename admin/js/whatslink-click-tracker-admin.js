(function( $ ) {
	'use strict';

	$(function() {

		const tableBody = document.querySelector('#whatslink-click-tracker-table tbody');
		if (!tableBody) return;

		const searchInput = document.querySelector('#whatslink-click-tracker-search');
		const pagination = document.querySelector('#whatslink-click-tracker-pagination');
		let currentOrderBy = 'click_datetime';
		let currentOrder = 'DESC';
		let currentPage = 1;

		function loadLogs(page = 1, search = '') {
			fetch(ajaxurl, {
				method: 'POST',
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
				body: new URLSearchParams({
					action: 'whatslink_click_tracker_get_click_logs',
					page,
					search,
					orderby: currentOrderBy,
					order: currentOrder,
					nonce: whatslink_click_tracker_admin.view_nonce
				})
			})
			.then(res => res.json())
			.then(result => {
				if (result.success && result.data.data.length > 0) {
					tableBody.innerHTML = '';
					const headers = document.querySelectorAll('#whatslink-click-tracker-table thead th');
					result.data.data.forEach(row => {
						let tr = '<tr>';
						headers.forEach(th => {
							console.log(th);
							
							const key = th.dataset.orderby;
							console.log('key', key);
							
							if (!key) return;
							
							let value = row[key] ?? '';
							console.log('val', value);
							if (key === 'permalink') {
								value = `<a href="${value}" target="_blank">${value}</a>`;
							}
							tr += `<td>${value}</td>`;
						});
						tr += '</tr>';
						// Append the row to the table body
						console.log(tr);
						
						tableBody.innerHTML += tr;
					});
					renderPagination(result.data.total, page);
					document.querySelector('#whatslink-click-tracker-total').textContent = `(${result.data.total} logs)`;
				} else {
					tableBody.innerHTML = `<tr><td colspan="4"><span class="dashicons dashicons-no-alt"></span> No results found</td></tr>`;
					pagination.innerHTML = '';
				}
			});
		}

		function renderPagination(total, current) {
			const totalPages = Math.ceil(total / 10);
			let buttons = '';
			for (let i = 1; i <= totalPages; i++) {
				buttons += `<button class="button ${i === current ? 'button-primary' : ''}" data-page="${i}">${i}</button> `;
			}
			pagination.innerHTML = buttons;
			document.querySelectorAll('#whatslink-click-tracker-pagination button').forEach(btn => {
				btn.addEventListener('click', () => {
					currentPage = parseInt(btn.getAttribute('data-page'));
					loadLogs(currentPage, searchInput.value);
				});
			});
		}
		if (searchInput) {
			searchInput.addEventListener('input', () => {
				currentPage = 1;
				loadLogs(currentPage, searchInput.value);
			});
		}

		loadLogs();

		document.querySelectorAll('th.sortable').forEach(th => {
			if (th.dataset.orderby === currentOrderBy) {
				th.classList.add('sorted', currentOrder.toLowerCase());
				th.querySelector('a').dataset.order = currentOrder;
			}
		});

		document.querySelectorAll('th.sortable a').forEach(link => {
			link.addEventListener('click', (e) => {
				e.preventDefault();
				const orderby = link.dataset.orderby;
				const isSame = orderby === currentOrderBy;

				currentOrderBy = orderby;
				currentOrder = isSame && currentOrder === 'ASC' ? 'DESC' : 'ASC';

				document.querySelectorAll('th.sortable').forEach(th => {
					th.classList.remove('sorted', 'asc', 'desc');
				});

				const th = link.closest('th');
				th.classList.add('sorted');
				th.classList.add(currentOrder.toLowerCase());

				link.dataset.order = currentOrder.toLowerCase();

				if (th.dataset.orderby === currentOrderBy) {
					th.classList.add('sorted', currentOrder.toLowerCase());
				}

				loadLogs(currentPage, searchInput.value);
			});
		});

		document.querySelector('#whatslink-click-tracker-reset')?.addEventListener('click', function () {
			if (!confirm('⚠️ Are you sure you want to delete all logged data? This action cannot be undone.')) {
				return;
			}

			const resetBtn = document.querySelector('#whatslink-click-tracker-reset');
			resetBtn.disabled = true;

			fetch(ajaxurl, {
				method: 'POST',
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
				body: new URLSearchParams({
					action: 'whatslink_click_tracker_reset_clicks',
					nonce: whatslink_click_tracker_admin.reset_nonce
				})
			})
			.then(res => res.json())
			.then(result => {
				if (result.success) {
					const notice = document.querySelector('#whatslink-click-tracker-notice');
					notice.innerHTML = `<div class="notice notice-success is-dismissible"><p>✅ Data has been reset.</p></div>`;
					currentPage = 1;
					loadLogs();
				} else {
					alert('❌ Error: ' + (result.data?.message || 'Could not reset logs.'));
				}
			})
			.finally(() => {
				resetBtn.disabled = false;
			});
		});

	});

})( jQuery );
