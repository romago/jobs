var Grnhse = Grnhse || {};
Grnhse.Settings = {
  targetDomain:   'https://boards.greenhouse.io',
  scrollOnLoad:   true,
  autoLoad:       true,
  boardURI:       'https://boards.greenhouse.io/embed/job_board?for=signifyd95',
  applicationURI: 'https://boards.greenhouse.io/embed/job_app?for=signifyd95',
  baseURI:        'https://boards.greenhouse.io/signifyd95',
  iFrameWidth:    '100%'
};
Grnhse.Config = {
  IframeDefault: {
    id:          'grnhse_iframe',
    width:       Grnhse.Settings.iFrameWidth,
    frameborder: '0',
    scrolling:   'no',
    allow:       'geolocation',
    onload:      undefined,
    title:       'Import jobs Board'
  }
};
Grnhse.BrowserHelper = {
  supportsPostMessage: function() {
    return !(document.all && !window.atob);
  }
};
Grnhse.Iframe = function(src, overrides) {
  var settings = Grnhse.Settings,
      self = this;
  overrides = overrides || {};
  this.config = Grnhse.Config.IframeDefault;
  this.config.src = src;
  this.supportAwfulBrowsers();
  overrides['onload'] = settings.scrollOnLoad ? undefined : undefined;
  mergeOverrides.call(this);
  this.registerEventHandlers();
  this.htmlElement = this.build();
  this.render();
  function mergeOverrides() {
    for (var override in overrides) {
      if (overrides.hasOwnProperty(override)) {
        self.config[override] = overrides[override];
      }
    }
  }
};
Grnhse.Iframe.prototype.build = function() {
  var iframe = document.createElement('iframe'),
      config = this.config;
  for (var key in config) {
    if (config.hasOwnProperty(key)) {
      iframe.setAttribute(key, config[key]);
    }
  }
  return iframe;
};
Grnhse.Iframe.prototype.render = function() {
  let el = this.htmlElement,
      container = document.getElementById('grnhse_app'),
      rect = container.getBoundingClientRect().top + document.documentElement.scrollTop,
      top = Math.round(rect) - 150;
  container.innerHTML = '';
  setTimeout(function(){
    container.appendChild(el);
    jQuery("#spiner").fadeOut(500, function() { jQuery(this).remove(); });
  }, 3000);
  setTimeout(function(){
    el.setAttribute('onload','window.scrollTo({top: '+ top +', behavior: "smooth"})');
  }, 10000);
};
Grnhse.Iframe.prototype.registerEventHandlers = function() {
    var instance = this,
      resizeEvent = null;
    if (window.addEventListener) {
      window.addEventListener('message', resize, false);
      window.addEventListener('resize', windowResize, false);
    } else if (window.attachEvent) {
      window.attachEvent('onmessage', resize);
      window.attachEvent('onresize', windowResize);
    }
    function windowResize(e) {
      // Pass resize event from parent window to iframe
      clearTimeout(resizeEvent);
      resizeEvent = setTimeout(triggerResize, 200);
    }
    function triggerResize() {
      if (window.postMessage && instance.htmlElement) {
        instance.htmlElement.contentWindow.postMessage('resize', '*');
      }
    }
    function resize(e) {
      if (instance.htmlElement && e.origin === Grnhse.Settings.targetDomain && e.data > 0) {
        instance.htmlElement.setAttribute('height', e.data);
      }
    }
};
Grnhse.Iframe.prototype.supportAwfulBrowsers = function() {
  var browserHelper = Grnhse.BrowserHelper;
  if (!browserHelper.supportsPostMessage()) {
    this.config['scrolling'] = 'yes';
    this.config['height'] = 1000;
  }
};
Grnhse.Iframe.load = function() {
  let jobId = jobs_ajax_object.id_job;
  pathToLoad = Grnhse.Settings.applicationURI+'&token='+jobId;
  return new Grnhse.Iframe(pathToLoad);
};
var _grnhse = _grnhse || {};
_grnhse.load = Grnhse.Iframe.load;
Grnhse.Iframe.autoLoad = function() {
  Grnhse.Iframe.load();
};
jQuery('.anchor-form').on('click', function(){
  jQuery([document.documentElement, document.body]).animate({
    scrollTop: jQuery("#scroll-top").offset().top - 150
  }, 800);
});
(function() {
  if (Grnhse.Settings.autoLoad) {
    addEventListener('load', Grnhse.Iframe.autoLoad);
  }
})();
