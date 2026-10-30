const { test } = require('node:test');
const assert = require('node:assert/strict');
const template = require('./healthcare-clinic-landing-page.json');

test('Elementor import keeps every required tool and unique element IDs', () => {
  const ids = [];
  const codes = [];
  for (const section of template.content) {
    ids.push(section.id);
    for (const widget of section.elements) {
      ids.push(widget.id);
      if (widget.widgetType === 'shortcode') codes.push(widget.settings.shortcode);
    }
  }
  assert.equal(new Set(ids).size, ids.length);
  assert.deepEqual(codes, ['[hc_calculator]', '[hc_builder]', '[hc_packages]', '[hc_hmo]', '[hc_request]', '[hc_contact]']);
});
