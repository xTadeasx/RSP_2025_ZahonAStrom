// Auto skrytí alertů
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.alert[data-auto-hide]')
    .forEach((el) => setTimeout(() => el.classList.add('hide'), 2600));

  // Přidej * k labelům, které patří k required inputům
  const requiredInputs = document.querySelectorAll('input[required], textarea[required], select[required]');
  requiredInputs.forEach((input) => {
    const id = input.getAttribute('id');
    let label = null;
    // 1) preferenčně hledej label ve stejném formuláři
    if (id && input.form) {
      label = input.form.querySelector(`label[for="${id}"]`);
    }
    // 2) fallback: bez for – vezmi předchozí sourozenecký label
    if (!label && input.previousElementSibling && input.previousElementSibling.tagName === 'LABEL') {
      label = input.previousElementSibling;
    }
    if (label && !label.querySelector('.req')) {
      const star = document.createElement('span');
      star.className = 'req';
      star.textContent = ' *';
      label.appendChild(star);
    }
  });
});

