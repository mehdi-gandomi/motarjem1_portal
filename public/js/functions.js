function select(selector) {
  return document.querySelector(selector);
}
function selectAll(selector) {
  return document.querySelectorAll(selector);
}

function removeClassFromElements(elements,className){
  elements.forEach(function(el){
    el.classList.remove(className);
  });
}

function getNodeindex(elm) {
  var c = elm.parentNode.children, i = 0;
  for (; i < c.length; i++)
    if (c[i] == elm) return i;
}

function addListener(el, type, callback,allElements) {
  if(allElements===undefined){
    allElements=false;
  }
  if(allElements){
    elements =selectAll(el);  
    elements.forEach(function(el){el.addEventListener(type,callback)});
    return;
  }
  if (typeof el != "object") {
      el = select(el);
  }
  el.addEventListener(type, callback);
}
function createEl(tagName, classes, attributes) {
  if (classes === undefined) {
    classes = [];
  }
  if (attributes === undefined) {
    attributes = {};
  }

  el = document.createElement(tagName);
  if (classes.length > 0) {
    classNames = classes.join(" ");
    el.className = classNames;
  }

  if (attributes.length != {}) {
    attributesArray = Object.entries(attributes);
    attributesArray.forEach(function (item) {
      el.setAttribute(item[0], item[1]);
    });
  }
  return el;
}