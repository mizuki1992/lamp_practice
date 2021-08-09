const select = document.querySelector("#sort");

select.addEventListener('change', submitSortForm);

function submitSortForm() {
   document.sortForm.submit()
}