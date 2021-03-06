module.exports = function ($form) {
  "use strict";

  var postData = {};

  _.each($form.serializeArray(), function (el) {
    postData[el.name] = el.value;
  });

  return postData;
};
