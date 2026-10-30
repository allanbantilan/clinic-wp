function hcRecommend(age, height, weight, activity, family) {
  if (!(age >= 18 && age <= 120 && height >= 100 && height <= 250 && weight >= 25 && weight <= 350)) return null;
  const bmi = weight / (height / 100) ** 2;
  const key = age >= 50 || family ? 'executive' : age >= 35 || activity === 'low' || bmi < 18.5 || bmi >= 25 ? 'standard' : 'basic';
  return { bmi, key };
}

function hcTotal(key, chosen, prices) {
  return (prices.packages[key]?.price || 0) + chosen.reduce((sum, addon) => sum + (prices.packages[key]?.includes?.includes(addon) ? 0 : prices.addons[addon]?.price || 0), 0);
}

if (typeof module !== 'undefined') module.exports = { hcRecommend, hcTotal };
if (typeof document !== 'undefined') document.addEventListener('DOMContentLoaded', () => {
  const builder = document.querySelector('.hc-builder');
  const request = document.querySelector('.hc-request');
  const packages = window.hcPrices?.packages || {};
  const addons = window.hcPrices?.addons || {};
  const money = value => new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP', maximumFractionDigits: 0 }).format(value);

  function selection() {
    const key = builder?.querySelector('input[name="hc-package"]:checked')?.value || 'standard';
    const chosen = [...(builder?.querySelectorAll('input[name="hc-addon"]:checked') || [])].map(input => input.value);
    return { key, chosen, total: hcTotal(key, chosen, { packages, addons }) };
  }

  function updateRequestSummary() {
    if (!request) return;
    const key = request.querySelector('[name="package"]').value;
    const chosen = request.querySelector('[name="addons"]').value.split(',').filter(Boolean);
    request.querySelector('.hc-request-summary').textContent = `Selected: ${packages[key]?.name || key} — ${money(hcTotal(key, chosen, { packages, addons }))}${chosen.length ? ` with ${chosen.map(addon => addons[addon]?.name || addon).join(', ')}` : ''}`;
  }

  function updateBuilder() {
    if (!builder) return;
    const selected = builder.querySelector('input[name="hc-package"]:checked')?.value || 'standard';
    builder.querySelectorAll('input[name="hc-addon"]').forEach(input => {
      const included = packages[selected]?.includes?.includes(input.value);
      if (included) input.checked = false;
      input.disabled = Boolean(included);
      input.closest('label').querySelector('small').textContent = included ? 'Included' : `+ ${money(addons[input.value]?.price || 0)}`;
    });
    const { chosen, total } = selection();
    builder.querySelector('.hc-total strong').textContent = money(total);
    if (request) {
      request.querySelector('[name="package"]').value = selected;
      request.querySelector('[name="addons"]').value = chosen.join(',');
      updateRequestSummary();
    }
  }

  builder?.addEventListener('change', updateBuilder);
  updateBuilder();
  builder?.querySelector('[data-hc-request]')?.addEventListener('click', () => {
    if (!request) return;
    const { key, chosen } = selection();
    request.querySelector('[name="package"]').value = key;
    request.querySelector('[name="addons"]').value = chosen.join(',');
    updateRequestSummary();
    request.scrollIntoView({ behavior: matchMedia('(prefers-reduced-motion: reduce)').matches ? 'instant' : 'smooth' });
    request.querySelector('[name="name"]').focus({ preventScroll: true });
  });
  request?.querySelector('[name="package"]')?.addEventListener('change', () => {
    request.querySelector('[name="addons"]').value = '';
    updateRequestSummary();
  });
  document.querySelectorAll('[data-hc-package]').forEach(link => link.addEventListener('click', () => {
    if (request) {
      request.querySelector('[name="package"]').value = link.dataset.hcPackage;
      request.querySelector('[name="addons"]').value = '';
      updateRequestSummary();
    }
  }));

  const calculator = document.querySelector('.hc-calculator');
  calculator?.querySelector('[data-hc-calculate]')?.addEventListener('click', () => {
    const age = Number(calculator.querySelector('[name="age"]').value);
    const height = Number(calculator.querySelector('[name="height"]').value);
    const weight = Number(calculator.querySelector('[name="weight"]').value);
    const result = calculator.querySelector('.hc-result');
    const suggestion = hcRecommend(age, height, weight, calculator.querySelector('[name="activity"]').value, calculator.querySelector('[name="family"]').checked);
    if (!suggestion) {
      result.textContent = 'Please enter an age from 18–120, height from 100–250 cm, and weight from 25–350 kg.';
      return;
    }
    const { bmi, key } = suggestion;
    result.textContent = `Your BMI is ${bmi.toFixed(1)}. You might start with the ${packages[key]?.name || key} package. A clinician can help choose appropriate tests.`;
    if (builder) {
      const radio = builder.querySelector(`input[name="hc-package"][value="${key}"]`);
      if (radio) { radio.checked = true; updateBuilder(); }
    }
  });

  const hmo = document.querySelector('.hc-hmo');
  hmo?.querySelector('input[type="search"]')?.addEventListener('input', event => {
    const query = event.target.value.trim().toLocaleLowerCase();
    let visible = 0;
    hmo.querySelectorAll('li').forEach(item => {
      item.hidden = !item.textContent.toLocaleLowerCase().includes(query);
      if (!item.hidden) visible++;
    });
    hmo.querySelector('.hc-hmo-empty').hidden = visible > 0;
  });
});
