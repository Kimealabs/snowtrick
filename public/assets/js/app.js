
let el = document.querySelector('#start')
let items = el.querySelectorAll('.text-info')

window.addEventListener('scroll', function (event) {
if (window.pageYOffset > 350) {
el.classList.remove('bg-secondary')
items.forEach((item) => {
item.classList.remove('text-info')
})
} else {
el.classList.add('bg-secondary')
items.forEach((item) => {
item.classList.add('text-info')
})

}
})