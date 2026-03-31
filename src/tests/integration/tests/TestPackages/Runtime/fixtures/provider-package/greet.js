/**
 * Test action: returns a greeting string.
 * Used by the integration test to verify qit.actions() works end-to-end.
 */
function testGreet(name) {
  return 'hello ' + name;
}

module.exports = testGreet;
module.exports.default = testGreet;
