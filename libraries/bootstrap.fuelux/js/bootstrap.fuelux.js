/*!
 * Bootstrap v3.3.7 (http://getbootstrap.com)
 * Copyright 2011-2016 Twitter, Inc.
 * Licensed under the MIT license
 */

if (typeof jQuery === 'undefined') {
  throw new Error('Bootstrap\'s JavaScript requires jQuery')
}

+function ($) {
  'use strict';
  var version = $.fn.jquery.split(' ')[0].split('.')
  if ((version[0] < 2 && version[1] < 9) || (version[0] == 1 && version[1] == 9 && version[2] < 1) || (version[0] > 3)) {
    throw new Error('Bootstrap\'s JavaScript requires jQuery version 1.9.1 or higher, but lower than version 4')
  }
}(jQuery);

/* ========================================================================
 * Bootstrap: transition.js v3.3.7
 * http://getbootstrap.com/javascript/#transitions
 * ========================================================================
 * Copyright 2011-2016 Twitter, Inc.
 * Licensed under MIT (https://github.com/twbs/bootstrap/blob/master/LICENSE)
 * ======================================================================== */


+function ($) {
  'use strict';

  // CSS TRANSITION SUPPORT (Shoutout: http://www.modernizr.com/)
  // ============================================================

  function transitionEnd() {
    var el = document.createElement('bootstrap')

    var transEndEventNames = {
      WebkitTransition : 'webkitTransitionEnd',
      MozTransition    : 'transitionend',
      OTransition      : 'oTransitionEnd otransitionend',
      transition       : 'transitionend'
    }

    for (var name in transEndEventNames) {
      if (el.style[name] !== undefined) {
        return { end: transEndEventNames[name] }
      }
    }

    return false // explicit for ie8 (  ._.)
  }

  // http://blog.alexmaccaw.com/css-transitions
  $.fn.emulateTransitionEnd = function (duration) {
    var called = false
    var $el = this
    $(this).one('bsTransitionEnd', function () { called = true })
    var callback = function () { if (!called) $($el).trigger($.support.transition.end) }
    setTimeout(callback, duration)
    return this
  }

  $(function () {
    $.support.transition = transitionEnd()

    if (!$.support.transition) return

    $.event.special.bsTransitionEnd = {
      bindType: $.support.transition.end,
      delegateType: $.support.transition.end,
      handle: function (e) {
        if ($(e.target).is(this)) return e.handleObj.handler.apply(this, arguments)
      }
    }
  })

}(jQuery);

/* ========================================================================
 * Bootstrap: alert.js v3.3.7
 * http://getbootstrap.com/javascript/#alerts
 * ========================================================================
 * Copyright 2011-2016 Twitter, Inc.
 * Licensed under MIT (https://github.com/twbs/bootstrap/blob/master/LICENSE)
 * ======================================================================== */


+function ($) {
  'use strict';

  // ALERT CLASS DEFINITION
  // ======================

  var dismiss = '[data-dismiss="alert"]'
  var Alert   = function (el) {
    $(el).on('click', dismiss, this.close)
  }

  Alert.VERSION = '3.3.7'

  Alert.TRANSITION_DURATION = 150

  Alert.prototype.close = function (e) {
    var $this    = $(this)
    var selector = $this.attr('data-target')

    if (!selector) {
      selector = $this.attr('href')
      selector = selector && selector.replace(/.*(?=#[^\s]*$)/, '') // strip for ie7
    }

    var $parent = $(selector === '#' ? [] : selector)

    if (e) e.preventDefault()

    if (!$parent.length) {
      $parent = $this.closest('.alert')
    }

    $parent.trigger(e = $.Event('close.bs.alert'))

    if (e.isDefaultPrevented()) return

    $parent.removeClass('in')

    function removeElement() {
      // detach from parent, fire event then clean up data
      $parent.detach().trigger('closed.bs.alert').remove()
    }

    $.support.transition && $parent.hasClass('fade') ?
      $parent
        .one('bsTransitionEnd', removeElement)
        .emulateTransitionEnd(Alert.TRANSITION_DURATION) :
      removeElement()
  }


  // ALERT PLUGIN DEFINITION
  // =======================

  function Plugin(option) {
    return this.each(function () {
      var $this = $(this)
      var data  = $this.data('bs.alert')

      if (!data) $this.data('bs.alert', (data = new Alert(this)))
      if (typeof option == 'string') data[option].call($this)
    })
  }

  var old = $.fn.alert

  $.fn.alert             = Plugin
  $.fn.alert.Constructor = Alert


  // ALERT NO CONFLICT
  // =================

  $.fn.alert.noConflict = function () {
    $.fn.alert = old
    return this
  }


  // ALERT DATA-API
  // ==============

  $(document).on('click.bs.alert.data-api', dismiss, Alert.prototype.close)

}(jQuery);

/* ========================================================================
 * Bootstrap: button.js v3.3.7
 * http://getbootstrap.com/javascript/#buttons
 * ========================================================================
 * Copyright 2011-2016 Twitter, Inc.
 * Licensed under MIT (https://github.com/twbs/bootstrap/blob/master/LICENSE)
 * ======================================================================== */


+function ($) {
  'use strict';

  // BUTTON PUBLIC CLASS DEFINITION
  // ==============================

  var Button = function (element, options) {
    this.$element  = $(element)
    this.options   = $.extend({}, Button.DEFAULTS, options)
    this.isLoading = false
  }

  Button.VERSION  = '3.3.7'

  Button.DEFAULTS = {
    loadingText: 'loading...'
  }

  Button.prototype.setState = function (state) {
    var d    = 'disabled'
    var $el  = this.$element
    var val  = $el.is('input') ? 'val' : 'html'
    var data = $el.data()

    state += 'Text'

    if (data.resetText == null) $el.data('resetText', $el[val]())

    // push to event loop to allow forms to submit
    setTimeout($.proxy(function () {
      $el[val](data[state] == null ? this.options[state] : data[state])

      if (state == 'loadingText') {
        this.isLoading = true
        $el.addClass(d).attr(d, d).prop(d, true)
      } else if (this.isLoading) {
        this.isLoading = false
        $el.removeClass(d).removeAttr(d).prop(d, false)
      }
    }, this), 0)
  }

  Button.prototype.toggle = function () {
    var changed = true
    var $parent = this.$element.closest('[data-toggle="buttons"]')

    if ($parent.length) {
      var $input = this.$element.find('input')
      if ($input.prop('type') == 'radio') {
        if ($input.prop('checked')) changed = false
        $parent.find('.active').removeClass('active')
        this.$element.addClass('active')
      } else if ($input.prop('type') == 'checkbox') {
        if (($input.prop('checked')) !== this.$element.hasClass('active')) changed = false
        this.$element.toggleClass('active')
      }
      $input.prop('checked', this.$element.hasClass('active'))
      if (changed) $input.trigger('change')
    } else {
      this.$element.attr('aria-pressed', !this.$element.hasClass('active'))
      this.$element.toggleClass('active')
    }
  }


  // BUTTON PLUGIN DEFINITION
  // ========================

  function Plugin(option) {
    return this.each(function () {
      var $this   = $(this)
      var data    = $this.data('bs.button')
      var options = typeof option == 'object' && option

      if (!data) $this.data('bs.button', (data = new Button(this, options)))

      if (option == 'toggle') data.toggle()
      else if (option) data.setState(option)
    })
  }

  var old = $.fn.button

  $.fn.button             = Plugin
  $.fn.button.Constructor = Button


  // BUTTON NO CONFLICT
  // ==================

  $.fn.button.noConflict = function () {
    $.fn.button = old
    return this
  }


  // BUTTON DATA-API
  // ===============

  $(document)
    .on('click.bs.button.data-api', '[data-toggle^="button"]', function (e) {
      var $btn = $(e.target).closest('.btn')
      Plugin.call($btn, 'toggle')
      if (!($(e.target).is('input[type="radio"], input[type="checkbox"]'))) {
        // Prevent double click on radios, and the double selections (so cancellation) on checkboxes
        e.preventDefault()
        // The target component still receive the focus
        if ($btn.is('input,button')) $btn.trigger('focus')
        else $btn.find('input:visible,button:visible').first().trigger('focus')
      }
    })
    .on('focus.bs.button.data-api blur.bs.button.data-api', '[data-toggle^="button"]', function (e) {
      $(e.target).closest('.btn').toggleClass('focus', /^focus(in)?$/.test(e.type))
    })

}(jQuery);

/* ========================================================================
 * Bootstrap: carousel.js v3.3.7
 * http://getbootstrap.com/javascript/#carousel
 * ========================================================================
 * Copyright 2011-2016 Twitter, Inc.
 * Licensed under MIT (https://github.com/twbs/bootstrap/blob/master/LICENSE)
 * ======================================================================== */


+function ($) {
  'use strict';

  // CAROUSEL CLASS DEFINITION
  // =========================

  var Carousel = function (element, options) {
    this.$element    = $(element)
    this.$indicators = this.$element.find('.carousel-indicators')
    this.options     = options
    this.paused      = null
    this.sliding     = null
    this.interval    = null
    this.$active     = null
    this.$items      = null

    this.options.keyboard && this.$element.on('keydown.bs.carousel', $.proxy(this.keydown, this))

    this.options.pause == 'hover' && !('ontouchstart' in document.documentElement) && this.$element
      .on('mouseenter.bs.carousel', $.proxy(this.pause, this))
      .on('mouseleave.bs.carousel', $.proxy(this.cycle, this))
  }

  Carousel.VERSION  = '3.3.7'

  Carousel.TRANSITION_DURATION = 600

  Carousel.DEFAULTS = {
    interval: 5000,
    pause: 'hover',
    wrap: true,
    keyboard: true
  }

  Carousel.prototype.keydown = function (e) {
    if (/input|textarea/i.test(e.target.tagName)) return
    switch (e.which) {
      case 37: this.prev(); break
      case 39: this.next(); break
      default: return
    }

    e.preventDefault()
  }

  Carousel.prototype.cycle = function (e) {
    e || (this.paused = false)

    this.interval && clearInterval(this.interval)

    this.options.interval
      && !this.paused
      && (this.interval = setInterval($.proxy(this.next, this), this.options.interval))

    return this
  }

  Carousel.prototype.getItemIndex = function (item) {
    this.$items = item.parent().children('.item')
    return this.$items.index(item || this.$active)
  }

  Carousel.prototype.getItemForDirection = function (direction, active) {
    var activeIndex = this.getItemIndex(active)
    var willWrap = (direction == 'prev' && activeIndex === 0)
                || (direction == 'next' && activeIndex == (this.$items.length - 1))
    if (willWrap && !this.options.wrap) return active
    var delta = direction == 'prev' ? -1 : 1
    var itemIndex = (activeIndex + delta) % this.$items.length
    return this.$items.eq(itemIndex)
  }

  Carousel.prototype.to = function (pos) {
    var that        = this
    var activeIndex = this.getItemIndex(this.$active = this.$element.find('.item.active'))

    if (pos > (this.$items.length - 1) || pos < 0) return

    if (this.sliding)       return this.$element.one('slid.bs.carousel', function () { that.to(pos) }) // yes, "slid"
    if (activeIndex == pos) return this.pause().cycle()

    return this.slide(pos > activeIndex ? 'next' : 'prev', this.$items.eq(pos))
  }

  Carousel.prototype.pause = function (e) {
    e || (this.paused = true)

    if (this.$element.find('.next, .prev').length && $.support.transition) {
      this.$element.trigger($.support.transition.end)
      this.cycle(true)
    }

    this.interval = clearInterval(this.interval)

    return this
  }

  Carousel.prototype.next = function () {
    if (this.sliding) return
    return this.slide('next')
  }

  Carousel.prototype.prev = function () {
    if (this.sliding) return
    return this.slide('prev')
  }

  Carousel.prototype.slide = function (type, next) {
    var $active   = this.$element.find('.item.active')
    var $next     = next || this.getItemForDirection(type, $active)
    var isCycling = this.interval
    var direction = type == 'next' ? 'left' : 'right'
    var that      = this

    if ($next.hasClass('active')) return (this.sliding = false)

    var relatedTarget = $next[0]
    var slideEvent = $.Event('slide.bs.carousel', {
      relatedTarget: relatedTarget,
      direction: direction
    })
    this.$element.trigger(slideEvent)
    if (slideEvent.isDefaultPrevented()) return

    this.sliding = true

    isCycling && this.pause()

    if (this.$indicators.length) {
      this.$indicators.find('.active').removeClass('active')
      var $nextIndicator = $(this.$indicators.children()[this.getItemIndex($next)])
      $nextIndicator && $nextIndicator.addClass('active')
    }

    var slidEvent = $.Event('slid.bs.carousel', { relatedTarget: relatedTarget, direction: direction }) // yes, "slid"
    if ($.support.transition && this.$element.hasClass('slide')) {
      $next.addClass(type)
      $next[0].offsetWidth // force reflow
      $active.addClass(direction)
      $next.addClass(direction)
      $active
        .one('bsTransitionEnd', function () {
          $next.removeClass([type, direction].join(' ')).addClass('active')
          $active.removeClass(['active', direction].join(' '))
          that.sliding = false
          setTimeout(function () {
            that.$element.trigger(slidEvent)
          }, 0)
        })
        .emulateTransitionEnd(Carousel.TRANSITION_DURATION)
    } else {
      $active.removeClass('active')
      $next.addClass('active')
      this.sliding = false
      this.$element.trigger(slidEvent)
    }

    isCycling && this.cycle()

    return this
  }


  // CAROUSEL PLUGIN DEFINITION
  // ==========================

  function Plugin(option) {
    return this.each(function () {
      var $this   = $(this)
      var data    = $this.data('bs.carousel')
      var options = $.extend({}, Carousel.DEFAULTS, $this.data(), typeof option == 'object' && option)
      var action  = typeof option == 'string' ? option : options.slide

      if (!data) $this.data('bs.carousel', (data = new Carousel(this, options)))
      if (typeof option == 'number') data.to(option)
      else if (action) data[action]()
      else if (options.interval) data.pause().cycle()
    })
  }

  var old = $.fn.carousel

  $.fn.carousel             = Plugin
  $.fn.carousel.Constructor = Carousel


  // CAROUSEL NO CONFLICT
  // ====================

  $.fn.carousel.noConflict = function () {
    $.fn.carousel = old
    return this
  }


  // CAROUSEL DATA-API
  // =================

  var clickHandler = function (e) {
    var href
    var $this   = $(this)
    var $target = $($this.attr('data-target') || (href = $this.attr('href')) && href.replace(/.*(?=#[^\s]+$)/, '')) // strip for ie7
    if (!$target.hasClass('carousel')) return
    var options = $.extend({}, $target.data(), $this.data())
    var slideIndex = $this.attr('data-slide-to')
    if (slideIndex) options.interval = false

    Plugin.call($target, options)

    if (slideIndex) {
      $target.data('bs.carousel').to(slideIndex)
    }

    e.preventDefault()
  }

  $(document)
    .on('click.bs.carousel.data-api', '[data-slide]', clickHandler)
    .on('click.bs.carousel.data-api', '[data-slide-to]', clickHandler)

  $(window).on('load', function () {
    $('[data-ride="carousel"]').each(function () {
      var $carousel = $(this)
      Plugin.call($carousel, $carousel.data())
    })
  })

}(jQuery);

/* ========================================================================
 * Bootstrap: collapse.js v3.3.7
 * http://getbootstrap.com/javascript/#collapse
 * ========================================================================
 * Copyright 2011-2016 Twitter, Inc.
 * Licensed under MIT (https://github.com/twbs/bootstrap/blob/master/LICENSE)
 * ======================================================================== */

/* jshint latedef: false */

+function ($) {
  'use strict';

  // COLLAPSE PUBLIC CLASS DEFINITION
  // ================================

  var Collapse = function (element, options) {
    this.$element      = $(element)
    this.options       = $.extend({}, Collapse.DEFAULTS, options)
    this.$trigger      = $('[data-toggle="collapse"][href="#' + element.id + '"],' +
                           '[data-toggle="collapse"][data-target="#' + element.id + '"]')
    this.transitioning = null

    if (this.options.parent) {
      this.$parent = this.getParent()
    } else {
      this.addAriaAndCollapsedClass(this.$element, this.$trigger)
    }

    if (this.options.toggle) this.toggle()
  }

  Collapse.VERSION  = '3.3.7'

  Collapse.TRANSITION_DURATION = 350

  Collapse.DEFAULTS = {
    toggle: true
  }

  Collapse.prototype.dimension = function () {
    var hasWidth = this.$element.hasClass('width')
    return hasWidth ? 'width' : 'height'
  }

  Collapse.prototype.show = function () {
    if (this.transitioning || this.$element.hasClass('in')) return

    var activesData
    var actives = this.$parent && this.$parent.children('.panel').children('.in, .collapsing')

    if (actives && actives.length) {
      activesData = actives.data('bs.collapse')
      if (activesData && activesData.transitioning) return
    }

    var startEvent = $.Event('show.bs.collapse')
    this.$element.trigger(startEvent)
    if (startEvent.isDefaultPrevented()) return

    if (actives && actives.length) {
      Plugin.call(actives, 'hide')
      activesData || actives.data('bs.collapse', null)
    }

    var dimension = this.dimension()

    this.$element
      .removeClass('collapse')
      .addClass('collapsing')[dimension](0)
      .attr('aria-expanded', true)

    this.$trigger
      .removeClass('collapsed')
      .attr('aria-expanded', true)

    this.transitioning = 1

    var complete = function () {
      this.$element
        .removeClass('collapsing')
        .addClass('collapse in')[dimension]('')
      this.transitioning = 0
      this.$element
        .trigger('shown.bs.collapse')
    }

    if (!$.support.transition) return complete.call(this)

    var scrollSize = $.camelCase(['scroll', dimension].join('-'))

    this.$element
      .one('bsTransitionEnd', $.proxy(complete, this))
      .emulateTransitionEnd(Collapse.TRANSITION_DURATION)[dimension](this.$element[0][scrollSize])
  }

  Collapse.prototype.hide = function () {
    if (this.transitioning || !this.$element.hasClass('in')) return

    var startEvent = $.Event('hide.bs.collapse')
    this.$element.trigger(startEvent)
    if (startEvent.isDefaultPrevented()) return

    var dimension = this.dimension()

    this.$element[dimension](this.$element[dimension]())[0].offsetHeight

    this.$element
      .addClass('collapsing')
      .removeClass('collapse in')
      .attr('aria-expanded', false)

    this.$trigger
      .addClass('collapsed')
      .attr('aria-expanded', false)

    this.transitioning = 1

    var complete = function () {
      this.transitioning = 0
      this.$element
        .removeClass('collapsing')
        .addClass('collapse')
        .trigger('hidden.bs.collapse')
    }

    if (!$.support.transition) return complete.call(this)

    this.$element
      [dimension](0)
      .one('bsTransitionEnd', $.proxy(complete, this))
      .emulateTransitionEnd(Collapse.TRANSITION_DURATION)
  }

  Collapse.prototype.toggle = function () {
    this[this.$element.hasClass('in') ? 'hide' : 'show']()
  }

  Collapse.prototype.getParent = function () {
    return $(this.options.parent)
      .find('[data-toggle="collapse"][data-parent="' + this.options.parent + '"]')
      .each($.proxy(function (i, element) {
        var $element = $(element)
        this.addAriaAndCollapsedClass(getTargetFromTrigger($element), $element)
      }, this))
      .end()
  }

  Collapse.prototype.addAriaAndCollapsedClass = function ($element, $trigger) {
    var isOpen = $element.hasClass('in')

    $element.attr('aria-expanded', isOpen)
    $trigger
      .toggleClass('collapsed', !isOpen)
      .attr('aria-expanded', isOpen)
  }

  function getTargetFromTrigger($trigger) {
    var href
    var target = $trigger.attr('data-target')
      || (href = $trigger.attr('href')) && href.replace(/.*(?=#[^\s]+$)/, '') // strip for ie7

    return $(target)
  }


  // COLLAPSE PLUGIN DEFINITION
  // ==========================

  function Plugin(option) {
    return this.each(function () {
      var $this   = $(this)
      var data    = $this.data('bs.collapse')
      var options = $.extend({}, Collapse.DEFAULTS, $this.data(), typeof option == 'object' && option)

      if (!data && options.toggle && /show|hide/.test(option)) options.toggle = false
      if (!data) $this.data('bs.collapse', (data = new Collapse(this, options)))
      if (typeof option == 'string') data[option]()
    })
  }

  var old = $.fn.collapse

  $.fn.collapse             = Plugin
  $.fn.collapse.Constructor = Collapse


  // COLLAPSE NO CONFLICT
  // ====================

  $.fn.collapse.noConflict = function () {
    $.fn.collapse = old
    return this
  }


  // COLLAPSE DATA-API
  // =================

  $(document).on('click.bs.collapse.data-api', '[data-toggle="collapse"]', function (e) {
    var $this   = $(this)

    if (!$this.attr('data-target')) e.preventDefault()

    var $target = getTargetFromTrigger($this)
    var data    = $target.data('bs.collapse')
    var option  = data ? 'toggle' : $this.data()

    Plugin.call($target, option)
  })

}(jQuery);

/* ========================================================================
 * Bootstrap: dropdown.js v3.3.7
 * http://getbootstrap.com/javascript/#dropdowns
 * ========================================================================
 * Copyright 2011-2016 Twitter, Inc.
 * Licensed under MIT (https://github.com/twbs/bootstrap/blob/master/LICENSE)
 * ======================================================================== */


+function ($) {
  'use strict';

  // DROPDOWN CLASS DEFINITION
  // =========================

  var backdrop = '.dropdown-backdrop'
  var toggle   = '[data-toggle="dropdown"]'
  var Dropdown = function (element) {
    $(element).on('click.bs.dropdown', this.toggle)
  }

  Dropdown.VERSION = '3.3.7'

  function getParent($this) {
    var selector = $this.attr('data-target')

    if (!selector) {
      selector = $this.attr('href')
      selector = selector && /#[A-Za-z]/.test(selector) && selector.replace(/.*(?=#[^\s]*$)/, '') // strip for ie7
    }

    var $parent = selector && $(selector)

    return $parent && $parent.length ? $parent : $this.parent()
  }

  function clearMenus(e) {
    if (e && e.which === 3) return
    $(backdrop).remove()
    $(toggle).each(function () {
      var $this         = $(this)
      var $parent       = getParent($this)
      var relatedTarget = { relatedTarget: this }

      if (!$parent.hasClass('open')) return

      if (e && e.type == 'click' && /input|textarea/i.test(e.target.tagName) && $.contains($parent[0], e.target)) return

      $parent.trigger(e = $.Event('hide.bs.dropdown', relatedTarget))

      if (e.isDefaultPrevented()) return

      $this.attr('aria-expanded', 'false')
      $parent.removeClass('open').trigger($.Event('hidden.bs.dropdown', relatedTarget))
    })
  }

  Dropdown.prototype.toggle = function (e) {
    var $this = $(this)

    if ($this.is('.disabled, :disabled')) return

    var $parent  = getParent($this)
    var isActive = $parent.hasClass('open')

    clearMenus()

    if (!isActive) {
      if ('ontouchstart' in document.documentElement && !$parent.closest('.navbar-nav').length) {
        // if mobile we use a backdrop because click events don't delegate
        $(document.createElement('div'))
          .addClass('dropdown-backdrop')
          .insertAfter($(this))
          .on('click', clearMenus)
      }

      var relatedTarget = { relatedTarget: this }
      $parent.trigger(e = $.Event('show.bs.dropdown', relatedTarget))

      if (e.isDefaultPrevented()) return

      $this
        .trigger('focus')
        .attr('aria-expanded', 'true')

      $parent
        .toggleClass('open')
        .trigger($.Event('shown.bs.dropdown', relatedTarget))
    }

    return false
  }

  Dropdown.prototype.keydown = function (e) {
    if (!/(38|40|27|32)/.test(e.which) || /input|textarea/i.test(e.target.tagName)) return

    var $this = $(this)

    e.preventDefault()
    e.stopPropagation()

    if ($this.is('.disabled, :disabled')) return

    var $parent  = getParent($this)
    var isActive = $parent.hasClass('open')

    if (!isActive && e.which != 27 || isActive && e.which == 27) {
      if (e.which == 27) $parent.find(toggle).trigger('focus')
      return $this.trigger('click')
    }

    var desc = ' li:not(.disabled):visible a'
    var $items = $parent.find('.dropdown-menu' + desc)

    if (!$items.length) return

    var index = $items.index(e.target)

    if (e.which == 38 && index > 0)                 index--         // up
    if (e.which == 40 && index < $items.length - 1) index++         // down
    if (!~index)                                    index = 0

    $items.eq(index).trigger('focus')
  }


  // DROPDOWN PLUGIN DEFINITION
  // ==========================

  function Plugin(option) {
    return this.each(function () {
      var $this = $(this)
      var data  = $this.data('bs.dropdown')

      if (!data) $this.data('bs.dropdown', (data = new Dropdown(this)))
      if (typeof option == 'string') data[option].call($this)
    })
  }

  var old = $.fn.dropdown

  $.fn.dropdown             = Plugin
  $.fn.dropdown.Constructor = Dropdown


  // DROPDOWN NO CONFLICT
  // ====================

  $.fn.dropdown.noConflict = function () {
    $.fn.dropdown = old
    return this
  }


  // APPLY TO STANDARD DROPDOWN ELEMENTS
  // ===================================

  $(document)
    .on('click.bs.dropdown.data-api', clearMenus)
    .on('click.bs.dropdown.data-api', '.dropdown form', function (e) { e.stopPropagation() })
    .on('click.bs.dropdown.data-api', toggle, Dropdown.prototype.toggle)
    .on('keydown.bs.dropdown.data-api', toggle, Dropdown.prototype.keydown)
    .on('keydown.bs.dropdown.data-api', '.dropdown-menu', Dropdown.prototype.keydown)

}(jQuery);

/* ========================================================================
 * Bootstrap: modal.js v3.3.7
 * http://getbootstrap.com/javascript/#modals
 * ========================================================================
 * Copyright 2011-2016 Twitter, Inc.
 * Licensed under MIT (https://github.com/twbs/bootstrap/blob/master/LICENSE)
 * ======================================================================== */


+function ($) {
  'use strict';

  // MODAL CLASS DEFINITION
  // ======================

  var Modal = function (element, options) {
    this.options             = options
    this.$body               = $(document.body)
    this.$element            = $(element)
    this.$dialog             = this.$element.find('.modal-dialog')
    this.$backdrop           = null
    this.isShown             = null
    this.originalBodyPad     = null
    this.scrollbarWidth      = 0
    this.ignoreBackdropClick = false

    if (this.options.remote) {
      this.$element
        .find('.modal-content')
        .load(this.options.remote, $.proxy(function () {
          this.$element.trigger('loaded.bs.modal')
        }, this))
    }
  }

  Modal.VERSION  = '3.3.7'

  Modal.TRANSITION_DURATION = 300
  Modal.BACKDROP_TRANSITION_DURATION = 150

  Modal.DEFAULTS = {
    backdrop: true,
    keyboard: true,
    show: true
  }

  Modal.prototype.toggle = function (_relatedTarget) {
    return this.isShown ? this.hide() : this.show(_relatedTarget)
  }

  Modal.prototype.show = function (_relatedTarget) {
    var that = this
    var e    = $.Event('show.bs.modal', { relatedTarget: _relatedTarget })

    this.$element.trigger(e)

    if (this.isShown || e.isDefaultPrevented()) return

    this.isShown = true

    this.checkScrollbar()
    this.setScrollbar()
    this.$body.addClass('modal-open')

    this.escape()
    this.resize()

    this.$element.on('click.dismiss.bs.modal', '[data-dismiss="modal"]', $.proxy(this.hide, this))

    this.$dialog.on('mousedown.dismiss.bs.modal', function () {
      that.$element.one('mouseup.dismiss.bs.modal', function (e) {
        if ($(e.target).is(that.$element)) that.ignoreBackdropClick = true
      })
    })

    this.backdrop(function () {
      var transition = $.support.transition && that.$element.hasClass('fade')

      if (!that.$element.parent().length) {
        that.$element.appendTo(that.$body) // don't move modals dom position
      }

      that.$element
        .show()
        .scrollTop(0)

      that.adjustDialog()

      if (transition) {
        that.$element[0].offsetWidth // force reflow
      }

      that.$element.addClass('in')

      that.enforceFocus()

      var e = $.Event('shown.bs.modal', { relatedTarget: _relatedTarget })

      transition ?
        that.$dialog // wait for modal to slide in
          .one('bsTransitionEnd', function () {
            that.$element.trigger('focus').trigger(e)
          })
          .emulateTransitionEnd(Modal.TRANSITION_DURATION) :
        that.$element.trigger('focus').trigger(e)
    })
  }

  Modal.prototype.hide = function (e) {
    if (e) e.preventDefault()

    e = $.Event('hide.bs.modal')

    this.$element.trigger(e)

    if (!this.isShown || e.isDefaultPrevented()) return

    this.isShown = false

    this.escape()
    this.resize()

    $(document).off('focusin.bs.modal')

    this.$element
      .removeClass('in')
      .off('click.dismiss.bs.modal')
      .off('mouseup.dismiss.bs.modal')

    this.$dialog.off('mousedown.dismiss.bs.modal')

    $.support.transition && this.$element.hasClass('fade') ?
      this.$element
        .one('bsTransitionEnd', $.proxy(this.hideModal, this))
        .emulateTransitionEnd(Modal.TRANSITION_DURATION) :
      this.hideModal()
  }

  Modal.prototype.enforceFocus = function () {
    $(document)
      .off('focusin.bs.modal') // guard against infinite focus loop
      .on('focusin.bs.modal', $.proxy(function (e) {
        if (document !== e.target &&
            this.$element[0] !== e.target &&
            !this.$element.has(e.target).length) {
          this.$element.trigger('focus')
        }
      }, this))
  }

  Modal.prototype.escape = function () {
    if (this.isShown && this.options.keyboard) {
      this.$element.on('keydown.dismiss.bs.modal', $.proxy(function (e) {
        e.which == 27 && this.hide()
      }, this))
    } else if (!this.isShown) {
      this.$element.off('keydown.dismiss.bs.modal')
    }
  }

  Modal.prototype.resize = function () {
    if (this.isShown) {
      $(window).on('resize.bs.modal', $.proxy(this.handleUpdate, this))
    } else {
      $(window).off('resize.bs.modal')
    }
  }

  Modal.prototype.hideModal = function () {
    var that = this
    this.$element.hide()
    this.backdrop(function () {
      that.$body.removeClass('modal-open')
      that.resetAdjustments()
      that.resetScrollbar()
      that.$element.trigger('hidden.bs.modal')
    })
  }

  Modal.prototype.removeBackdrop = function () {
    this.$backdrop && this.$backdrop.remove()
    this.$backdrop = null
  }

  Modal.prototype.backdrop = function (callback) {
    var that = this
    var animate = this.$element.hasClass('fade') ? 'fade' : ''

    if (this.isShown && this.options.backdrop) {
      var doAnimate = $.support.transition && animate

      this.$backdrop = $(document.createElement('div'))
        .addClass('modal-backdrop ' + animate)
        .appendTo(this.$body)

      this.$element.on('click.dismiss.bs.modal', $.proxy(function (e) {
        if (this.ignoreBackdropClick) {
          this.ignoreBackdropClick = false
          return
        }
        if (e.target !== e.currentTarget) return
        this.options.backdrop == 'static'
          ? this.$element[0].focus()
          : this.hide()
      }, this))

      if (doAnimate) this.$backdrop[0].offsetWidth // force reflow

      this.$backdrop.addClass('in')

      if (!callback) return

      doAnimate ?
        this.$backdrop
          .one('bsTransitionEnd', callback)
          .emulateTransitionEnd(Modal.BACKDROP_TRANSITION_DURATION) :
        callback()

    } else if (!this.isShown && this.$backdrop) {
      this.$backdrop.removeClass('in')

      var callbackRemove = function () {
        that.removeBackdrop()
        callback && callback()
      }
      $.support.transition && this.$element.hasClass('fade') ?
        this.$backdrop
          .one('bsTransitionEnd', callbackRemove)
          .emulateTransitionEnd(Modal.BACKDROP_TRANSITION_DURATION) :
        callbackRemove()

    } else if (callback) {
      callback()
    }
  }

  // these following methods are used to handle overflowing modals

  Modal.prototype.handleUpdate = function () {
    this.adjustDialog()
  }

  Modal.prototype.adjustDialog = function () {
    var modalIsOverflowing = this.$element[0].scrollHeight > document.documentElement.clientHeight

    this.$element.css({
      paddingLeft:  !this.bodyIsOverflowing && modalIsOverflowing ? this.scrollbarWidth : '',
      paddingRight: this.bodyIsOverflowing && !modalIsOverflowing ? this.scrollbarWidth : ''
    })
  }

  Modal.prototype.resetAdjustments = function () {
    this.$element.css({
      paddingLeft: '',
      paddingRight: ''
    })
  }

  Modal.prototype.checkScrollbar = function () {
    var fullWindowWidth = window.innerWidth
    if (!fullWindowWidth) { // workaround for missing window.innerWidth in IE8
      var documentElementRect = document.documentElement.getBoundingClientRect()
      fullWindowWidth = documentElementRect.right - Math.abs(documentElementRect.left)
    }
    this.bodyIsOverflowing = document.body.clientWidth < fullWindowWidth
    this.scrollbarWidth = this.measureScrollbar()
  }

  Modal.prototype.setScrollbar = function () {
    var bodyPad = parseInt((this.$body.css('padding-right') || 0), 10)
    this.originalBodyPad = document.body.style.paddingRight || ''
    if (this.bodyIsOverflowing) this.$body.css('padding-right', bodyPad + this.scrollbarWidth)
  }

  Modal.prototype.resetScrollbar = function () {
    this.$body.css('padding-right', this.originalBodyPad)
  }

  Modal.prototype.measureScrollbar = function () { // thx walsh
    var scrollDiv = document.createElement('div')
    scrollDiv.className = 'modal-scrollbar-measure'
    this.$body.append(scrollDiv)
    var scrollbarWidth = scrollDiv.offsetWidth - scrollDiv.clientWidth
    this.$body[0].removeChild(scrollDiv)
    return scrollbarWidth
  }


  // MODAL PLUGIN DEFINITION
  // =======================

  function Plugin(option, _relatedTarget) {
    return this.each(function () {
      var $this   = $(this)
      var data    = $this.data('bs.modal')
      var options = $.extend({}, Modal.DEFAULTS, $this.data(), typeof option == 'object' && option)

      if (!data) $this.data('bs.modal', (data = new Modal(this, options)))
      if (typeof option == 'string') data[option](_relatedTarget)
      else if (options.show) data.show(_relatedTarget)
    })
  }

  var old = $.fn.modal

  $.fn.modal             = Plugin
  $.fn.modal.Constructor = Modal


  // MODAL NO CONFLICT
  // =================

  $.fn.modal.noConflict = function () {
    $.fn.modal = old
    return this
  }


  // MODAL DATA-API
  // ==============

  $(document).on('click.bs.modal.data-api', '[data-toggle="modal"]', function (e) {
    var $this   = $(this)
    var href    = $this.attr('href')
    var $target = $($this.attr('data-target') || (href && href.replace(/.*(?=#[^\s]+$)/, ''))) // strip for ie7
    var option  = $target.data('bs.modal') ? 'toggle' : $.extend({ remote: !/#/.test(href) && href }, $target.data(), $this.data())

    if ($this.is('a')) e.preventDefault()

    $target.one('show.bs.modal', function (showEvent) {
      if (showEvent.isDefaultPrevented()) return // only register focus restorer if modal will actually get shown
      $target.one('hidden.bs.modal', function () {
        $this.is(':visible') && $this.trigger('focus')
      })
    })
    Plugin.call($target, option, this)
  })

}(jQuery);

/* ========================================================================
 * Bootstrap: tooltip.js v3.3.7
 * http://getbootstrap.com/javascript/#tooltip
 * Inspired by the original jQuery.tipsy by Jason Frame
 * ========================================================================
 * Copyright 2011-2016 Twitter, Inc.
 * Licensed under MIT (https://github.com/twbs/bootstrap/blob/master/LICENSE)
 * ======================================================================== */


+function ($) {
  'use strict';

  // TOOLTIP PUBLIC CLASS DEFINITION
  // ===============================

  var Tooltip = function (element, options) {
    this.type       = null
    this.options    = null
    this.enabled    = null
    this.timeout    = null
    this.hoverState = null
    this.$element   = null
    this.inState    = null

    this.init('tooltip', element, options)
  }

  Tooltip.VERSION  = '3.3.7'

  Tooltip.TRANSITION_DURATION = 150

  Tooltip.DEFAULTS = {
    animation: true,
    placement: 'top',
    selector: false,
    template: '<div class="tooltip" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>',
    trigger: 'hover focus',
    title: '',
    delay: 0,
    html: false,
    container: false,
    viewport: {
      selector: 'body',
      padding: 0
    }
  }

  Tooltip.prototype.init = function (type, element, options) {
    this.enabled   = true
    this.type      = type
    this.$element  = $(element)
    this.options   = this.getOptions(options)
    this.$viewport = this.options.viewport && $($.isFunction(this.options.viewport) ? this.options.viewport.call(this, this.$element) : (this.options.viewport.selector || this.options.viewport))
    this.inState   = { click: false, hover: false, focus: false }

    if (this.$element[0] instanceof document.constructor && !this.options.selector) {
      throw new Error('`selector` option must be specified when initializing ' + this.type + ' on the window.document object!')
    }

    var triggers = this.options.trigger.split(' ')

    for (var i = triggers.length; i--;) {
      var trigger = triggers[i]

      if (trigger == 'click') {
        this.$element.on('click.' + this.type, this.options.selector, $.proxy(this.toggle, this))
      } else if (trigger != 'manual') {
        var eventIn  = trigger == 'hover' ? 'mouseenter' : 'focusin'
        var eventOut = trigger == 'hover' ? 'mouseleave' : 'focusout'

        this.$element.on(eventIn  + '.' + this.type, this.options.selector, $.proxy(this.enter, this))
        this.$element.on(eventOut + '.' + this.type, this.options.selector, $.proxy(this.leave, this))
      }
    }

    this.options.selector ?
      (this._options = $.extend({}, this.options, { trigger: 'manual', selector: '' })) :
      this.fixTitle()
  }

  Tooltip.prototype.getDefaults = function () {
    return Tooltip.DEFAULTS
  }

  Tooltip.prototype.getOptions = function (options) {
    options = $.extend({}, this.getDefaults(), this.$element.data(), options)

    if (options.delay && typeof options.delay == 'number') {
      options.delay = {
        show: options.delay,
        hide: options.delay
      }
    }

    return options
  }

  Tooltip.prototype.getDelegateOptions = function () {
    var options  = {}
    var defaults = this.getDefaults()

    this._options && $.each(this._options, function (key, value) {
      if (defaults[key] != value) options[key] = value
    })

    return options
  }

  Tooltip.prototype.enter = function (obj) {
    var self = obj instanceof this.constructor ?
      obj : $(obj.currentTarget).data('bs.' + this.type)

    if (!self) {
      self = new this.constructor(obj.currentTarget, this.getDelegateOptions())
      $(obj.currentTarget).data('bs.' + this.type, self)
    }

    if (obj instanceof $.Event) {
      self.inState[obj.type == 'focusin' ? 'focus' : 'hover'] = true
    }

    if (self.tip().hasClass('in') || self.hoverState == 'in') {
      self.hoverState = 'in'
      return
    }

    clearTimeout(self.timeout)

    self.hoverState = 'in'

    if (!self.options.delay || !self.options.delay.show) return self.show()

    self.timeout = setTimeout(function () {
      if (self.hoverState == 'in') self.show()
    }, self.options.delay.show)
  }

  Tooltip.prototype.isInStateTrue = function () {
    for (var key in this.inState) {
      if (this.inState[key]) return true
    }

    return false
  }

  Tooltip.prototype.leave = function (obj) {
    var self = obj instanceof this.constructor ?
      obj : $(obj.currentTarget).data('bs.' + this.type)

    if (!self) {
      self = new this.constructor(obj.currentTarget, this.getDelegateOptions())
      $(obj.currentTarget).data('bs.' + this.type, self)
    }

    if (obj instanceof $.Event) {
      self.inState[obj.type == 'focusout' ? 'focus' : 'hover'] = false
    }

    if (self.isInStateTrue()) return

    clearTimeout(self.timeout)

    self.hoverState = 'out'

    if (!self.options.delay || !self.options.delay.hide) return self.hide()

    self.timeout = setTimeout(function () {
      if (self.hoverState == 'out') self.hide()
    }, self.options.delay.hide)
  }

  Tooltip.prototype.show = function () {
    var e = $.Event('show.bs.' + this.type)

    if (this.hasContent() && this.enabled) {
      this.$element.trigger(e)

      var inDom = $.contains(this.$element[0].ownerDocument.documentElement, this.$element[0])
      if (e.isDefaultPrevented() || !inDom) return
      var that = this

      var $tip = this.tip()

      var tipId = this.getUID(this.type)

      this.setContent()
      $tip.attr('id', tipId)
      this.$element.attr('aria-describedby', tipId)

      if (this.options.animation) $tip.addClass('fade')

      var placement = typeof this.options.placement == 'function' ?
        this.options.placement.call(this, $tip[0], this.$element[0]) :
        this.options.placement

      var autoToken = /\s?auto?\s?/i
      var autoPlace = autoToken.test(placement)
      if (autoPlace) placement = placement.replace(autoToken, '') || 'top'

      $tip
        .detach()
        .css({ top: 0, left: 0, display: 'block' })
        .addClass(placement)
        .data('bs.' + this.type, this)

      this.options.container ? $tip.appendTo(this.options.container) : $tip.insertAfter(this.$element)
      this.$element.trigger('inserted.bs.' + this.type)

      var pos          = this.getPosition()
      var actualWidth  = $tip[0].offsetWidth
      var actualHeight = $tip[0].offsetHeight

      if (autoPlace) {
        var orgPlacement = placement
        var viewportDim = this.getPosition(this.$viewport)

        placement = placement == 'bottom' && pos.bottom + actualHeight > viewportDim.bottom ? 'top'    :
                    placement == 'top'    && pos.top    - actualHeight < viewportDim.top    ? 'bottom' :
                    placement == 'right'  && pos.right  + actualWidth  > viewportDim.width  ? 'left'   :
                    placement == 'left'   && pos.left   - actualWidth  < viewportDim.left   ? 'right'  :
                    placement

        $tip
          .removeClass(orgPlacement)
          .addClass(placement)
      }

      var calculatedOffset = this.getCalculatedOffset(placement, pos, actualWidth, actualHeight)

      this.applyPlacement(calculatedOffset, placement)

      var complete = function () {
        var prevHoverState = that.hoverState
        that.$element.trigger('shown.bs.' + that.type)
        that.hoverState = null

        if (prevHoverState == 'out') that.leave(that)
      }

      $.support.transition && this.$tip.hasClass('fade') ?
        $tip
          .one('bsTransitionEnd', complete)
          .emulateTransitionEnd(Tooltip.TRANSITION_DURATION) :
        complete()
    }
  }

  Tooltip.prototype.applyPlacement = function (offset, placement) {
    var $tip   = this.tip()
    var width  = $tip[0].offsetWidth
    var height = $tip[0].offsetHeight

    // manually read margins because getBoundingClientRect includes difference
    var marginTop = parseInt($tip.css('margin-top'), 10)
    var marginLeft = parseInt($tip.css('margin-left'), 10)

    // we must check for NaN for ie 8/9
    if (isNaN(marginTop))  marginTop  = 0
    if (isNaN(marginLeft)) marginLeft = 0

    offset.top  += marginTop
    offset.left += marginLeft

    // $.fn.offset doesn't round pixel values
    // so we use setOffset directly with our own function B-0
    $.offset.setOffset($tip[0], $.extend({
      using: function (props) {
        $tip.css({
          top: Math.round(props.top),
          left: Math.round(props.left)
        })
      }
    }, offset), 0)

    $tip.addClass('in')

    // check to see if placing tip in new offset caused the tip to resize itself
    var actualWidth  = $tip[0].offsetWidth
    var actualHeight = $tip[0].offsetHeight

    if (placement == 'top' && actualHeight != height) {
      offset.top = offset.top + height - actualHeight
    }

    var delta = this.getViewportAdjustedDelta(placement, offset, actualWidth, actualHeight)

    if (delta.left) offset.left += delta.left
    else offset.top += delta.top

    var isVertical          = /top|bottom/.test(placement)
    var arrowDelta          = isVertical ? delta.left * 2 - width + actualWidth : delta.top * 2 - height + actualHeight
    var arrowOffsetPosition = isVertical ? 'offsetWidth' : 'offsetHeight'

    $tip.offset(offset)
    this.replaceArrow(arrowDelta, $tip[0][arrowOffsetPosition], isVertical)
  }

  Tooltip.prototype.replaceArrow = function (delta, dimension, isVertical) {
    this.arrow()
      .css(isVertical ? 'left' : 'top', 50 * (1 - delta / dimension) + '%')
      .css(isVertical ? 'top' : 'left', '')
  }

  Tooltip.prototype.setContent = function () {
    var $tip  = this.tip()
    var title = this.getTitle()

    $tip.find('.tooltip-inner')[this.options.html ? 'html' : 'text'](title)
    $tip.removeClass('fade in top bottom left right')
  }

  Tooltip.prototype.hide = function (callback) {
    var that = this
    var $tip = $(this.$tip)
    var e    = $.Event('hide.bs.' + this.type)

    function complete() {
      if (that.hoverState != 'in') $tip.detach()
      if (that.$element) { // TODO: Check whether guarding this code with this `if` is really necessary.
        that.$element
          .removeAttr('aria-describedby')
          .trigger('hidden.bs.' + that.type)
      }
      callback && callback()
    }

    this.$element.trigger(e)

    if (e.isDefaultPrevented()) return

    $tip.removeClass('in')

    $.support.transition && $tip.hasClass('fade') ?
      $tip
        .one('bsTransitionEnd', complete)
        .emulateTransitionEnd(Tooltip.TRANSITION_DURATION) :
      complete()

    this.hoverState = null

    return this
  }

  Tooltip.prototype.fixTitle = function () {
    var $e = this.$element
    if ($e.attr('title') || typeof $e.attr('data-original-title') != 'string') {
      $e.attr('data-original-title', $e.attr('title') || '').attr('title', '')
    }
  }

  Tooltip.prototype.hasContent = function () {
    return this.getTitle()
  }

  Tooltip.prototype.getPosition = function ($element) {
    $element   = $element || this.$element

    var el     = $element[0]
    var isBody = el.tagName == 'BODY'

    var elRect    = el.getBoundingClientRect()
    if (elRect.width == null) {
      // width and height are missing in IE8, so compute them manually; see https://github.com/twbs/bootstrap/issues/14093
      elRect = $.extend({}, elRect, { width: elRect.right - elRect.left, height: elRect.bottom - elRect.top })
    }
    var isSvg = window.SVGElement && el instanceof window.SVGElement
    // Avoid using $.offset() on SVGs since it gives incorrect results in jQuery 3.
    // See https://github.com/twbs/bootstrap/issues/20280
    var elOffset  = isBody ? { top: 0, left: 0 } : (isSvg ? null : $element.offset())
    var scroll    = { scroll: isBody ? document.documentElement.scrollTop || document.body.scrollTop : $element.scrollTop() }
    var outerDims = isBody ? { width: $(window).width(), height: $(window).height() } : null

    return $.extend({}, elRect, scroll, outerDims, elOffset)
  }

  Tooltip.prototype.getCalculatedOffset = function (placement, pos, actualWidth, actualHeight) {
    return placement == 'bottom' ? { top: pos.top + pos.height,   left: pos.left + pos.width / 2 - actualWidth / 2 } :
           placement == 'top'    ? { top: pos.top - actualHeight, left: pos.left + pos.width / 2 - actualWidth / 2 } :
           placement == 'left'   ? { top: pos.top + pos.height / 2 - actualHeight / 2, left: pos.left - actualWidth } :
        /* placement == 'right' */ { top: pos.top + pos.height / 2 - actualHeight / 2, left: pos.left + pos.width }

  }

  Tooltip.prototype.getViewportAdjustedDelta = function (placement, pos, actualWidth, actualHeight) {
    var delta = { top: 0, left: 0 }
    if (!this.$viewport) return delta

    var viewportPadding = this.options.viewport && this.options.viewport.padding || 0
    var viewportDimensions = this.getPosition(this.$viewport)

    if (/right|left/.test(placement)) {
      var topEdgeOffset    = pos.top - viewportPadding - viewportDimensions.scroll
      var bottomEdgeOffset = pos.top + viewportPadding - viewportDimensions.scroll + actualHeight
      if (topEdgeOffset < viewportDimensions.top) { // top overflow
        delta.top = viewportDimensions.top - topEdgeOffset
      } else if (bottomEdgeOffset > viewportDimensions.top + viewportDimensions.height) { // bottom overflow
        delta.top = viewportDimensions.top + viewportDimensions.height - bottomEdgeOffset
      }
    } else {
      var leftEdgeOffset  = pos.left - viewportPadding
      var rightEdgeOffset = pos.left + viewportPadding + actualWidth
      if (leftEdgeOffset < viewportDimensions.left) { // left overflow
        delta.left = viewportDimensions.left - leftEdgeOffset
      } else if (rightEdgeOffset > viewportDimensions.right) { // right overflow
        delta.left = viewportDimensions.left + viewportDimensions.width - rightEdgeOffset
      }
    }

    return delta
  }

  Tooltip.prototype.getTitle = function () {
    var title
    var $e = this.$element
    var o  = this.options

    title = $e.attr('data-original-title')
      || (typeof o.title == 'function' ? o.title.call($e[0]) :  o.title)

    return title
  }

  Tooltip.prototype.getUID = function (prefix) {
    do prefix += ~~(Math.random() * 1000000)
    while (document.getElementById(prefix))
    return prefix
  }

  Tooltip.prototype.tip = function () {
    if (!this.$tip) {
      this.$tip = $(this.options.template)
      if (this.$tip.length != 1) {
        throw new Error(this.type + ' `template` option must consist of exactly 1 top-level element!')
      }
    }
    return this.$tip
  }

  Tooltip.prototype.arrow = function () {
    return (this.$arrow = this.$arrow || this.tip().find('.tooltip-arrow'))
  }

  Tooltip.prototype.enable = function () {
    this.enabled = true
  }

  Tooltip.prototype.disable = function () {
    this.enabled = false
  }

  Tooltip.prototype.toggleEnabled = function () {
    this.enabled = !this.enabled
  }

  Tooltip.prototype.toggle = function (e) {
    var self = this
    if (e) {
      self = $(e.currentTarget).data('bs.' + this.type)
      if (!self) {
        self = new this.constructor(e.currentTarget, this.getDelegateOptions())
        $(e.currentTarget).data('bs.' + this.type, self)
      }
    }

    if (e) {
      self.inState.click = !self.inState.click
      if (self.isInStateTrue()) self.enter(self)
      else self.leave(self)
    } else {
      self.tip().hasClass('in') ? self.leave(self) : self.enter(self)
    }
  }

  Tooltip.prototype.destroy = function () {
    var that = this
    clearTimeout(this.timeout)
    this.hide(function () {
      that.$element.off('.' + that.type).removeData('bs.' + that.type)
      if (that.$tip) {
        that.$tip.detach()
      }
      that.$tip = null
      that.$arrow = null
      that.$viewport = null
      that.$element = null
    })
  }


  // TOOLTIP PLUGIN DEFINITION
  // =========================

  function Plugin(option) {
    return this.each(function () {
      var $this   = $(this)
      var data    = $this.data('bs.tooltip')
      var options = typeof option == 'object' && option

      if (!data && /destroy|hide/.test(option)) return
      if (!data) $this.data('bs.tooltip', (data = new Tooltip(this, options)))
      if (typeof option == 'string') data[option]()
    })
  }

  var old = $.fn.tooltip

  $.fn.tooltip             = Plugin
  $.fn.tooltip.Constructor = Tooltip


  // TOOLTIP NO CONFLICT
  // ===================

  $.fn.tooltip.noConflict = function () {
    $.fn.tooltip = old
    return this
  }

}(jQuery);

/* ========================================================================
 * Bootstrap: popover.js v3.3.7
 * http://getbootstrap.com/javascript/#popovers
 * ========================================================================
 * Copyright 2011-2016 Twitter, Inc.
 * Licensed under MIT (https://github.com/twbs/bootstrap/blob/master/LICENSE)
 * ======================================================================== */


+function ($) {
  'use strict';

  // POPOVER PUBLIC CLASS DEFINITION
  // ===============================

  var Popover = function (element, options) {
    this.init('popover', element, options)
  }

  if (!$.fn.tooltip) throw new Error('Popover requires tooltip.js')

  Popover.VERSION  = '3.3.7'

  Popover.DEFAULTS = $.extend({}, $.fn.tooltip.Constructor.DEFAULTS, {
    placement: 'right',
    trigger: 'click',
    content: '',
    template: '<div class="popover" role="tooltip"><div class="arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>'
  })


  // NOTE: POPOVER EXTENDS tooltip.js
  // ================================

  Popover.prototype = $.extend({}, $.fn.tooltip.Constructor.prototype)

  Popover.prototype.constructor = Popover

  Popover.prototype.getDefaults = function () {
    return Popover.DEFAULTS
  }

  Popover.prototype.setContent = function () {
    var $tip    = this.tip()
    var title   = this.getTitle()
    var content = this.getContent()

    $tip.find('.popover-title')[this.options.html ? 'html' : 'text'](title)
    $tip.find('.popover-content').children().detach().end()[ // we use append for html objects to maintain js events
      this.options.html ? (typeof content == 'string' ? 'html' : 'append') : 'text'
    ](content)

    $tip.removeClass('fade top bottom left right in')

    // IE8 doesn't accept hiding via the `:empty` pseudo selector, we have to do
    // this manually by checking the contents.
    if (!$tip.find('.popover-title').html()) $tip.find('.popover-title').hide()
  }

  Popover.prototype.hasContent = function () {
    return this.getTitle() || this.getContent()
  }

  Popover.prototype.getContent = function () {
    var $e = this.$element
    var o  = this.options

    return $e.attr('data-content')
      || (typeof o.content == 'function' ?
            o.content.call($e[0]) :
            o.content)
  }

  Popover.prototype.arrow = function () {
    return (this.$arrow = this.$arrow || this.tip().find('.arrow'))
  }


  // POPOVER PLUGIN DEFINITION
  // =========================

  function Plugin(option) {
    return this.each(function () {
      var $this   = $(this)
      var data    = $this.data('bs.popover')
      var options = typeof option == 'object' && option

      if (!data && /destroy|hide/.test(option)) return
      if (!data) $this.data('bs.popover', (data = new Popover(this, options)))
      if (typeof option == 'string') data[option]()
    })
  }

  var old = $.fn.popover

  $.fn.popover             = Plugin
  $.fn.popover.Constructor = Popover


  // POPOVER NO CONFLICT
  // ===================

  $.fn.popover.noConflict = function () {
    $.fn.popover = old
    return this
  }

}(jQuery);

/* ========================================================================
 * Bootstrap: scrollspy.js v3.3.7
 * http://getbootstrap.com/javascript/#scrollspy
 * ========================================================================
 * Copyright 2011-2016 Twitter, Inc.
 * Licensed under MIT (https://github.com/twbs/bootstrap/blob/master/LICENSE)
 * ======================================================================== */


+function ($) {
  'use strict';

  // SCROLLSPY CLASS DEFINITION
  // ==========================

  function ScrollSpy(element, options) {
    this.$body          = $(document.body)
    this.$scrollElement = $(element).is(document.body) ? $(window) : $(element)
    this.options        = $.extend({}, ScrollSpy.DEFAULTS, options)
    this.selector       = (this.options.target || '') + ' .nav li > a'
    this.offsets        = []
    this.targets        = []
    this.activeTarget   = null
    this.scrollHeight   = 0

    this.$scrollElement.on('scroll.bs.scrollspy', $.proxy(this.process, this))
    this.refresh()
    this.process()
  }

  ScrollSpy.VERSION  = '3.3.7'

  ScrollSpy.DEFAULTS = {
    offset: 10
  }

  ScrollSpy.prototype.getScrollHeight = function () {
    return this.$scrollElement[0].scrollHeight || Math.max(this.$body[0].scrollHeight, document.documentElement.scrollHeight)
  }

  ScrollSpy.prototype.refresh = function () {
    var that          = this
    var offsetMethod  = 'offset'
    var offsetBase    = 0

    this.offsets      = []
    this.targets      = []
    this.scrollHeight = this.getScrollHeight()

    if (!$.isWindow(this.$scrollElement[0])) {
      offsetMethod = 'position'
      offsetBase   = this.$scrollElement.scrollTop()
    }

    this.$body
      .find(this.selector)
      .map(function () {
        var $el   = $(this)
        var href  = $el.data('target') || $el.attr('href')
        var $href = /^#./.test(href) && $(href)

        return ($href
          && $href.length
          && $href.is(':visible')
          && [[$href[offsetMethod]().top + offsetBase, href]]) || null
      })
      .sort(function (a, b) { return a[0] - b[0] })
      .each(function () {
        that.offsets.push(this[0])
        that.targets.push(this[1])
      })
  }

  ScrollSpy.prototype.process = function () {
    var scrollTop    = this.$scrollElement.scrollTop() + this.options.offset
    var scrollHeight = this.getScrollHeight()
    var maxScroll    = this.options.offset + scrollHeight - this.$scrollElement.height()
    var offsets      = this.offsets
    var targets      = this.targets
    var activeTarget = this.activeTarget
    var i

    if (this.scrollHeight != scrollHeight) {
      this.refresh()
    }

    if (scrollTop >= maxScroll) {
      return activeTarget != (i = targets[targets.length - 1]) && this.activate(i)
    }

    if (activeTarget && scrollTop < offsets[0]) {
      this.activeTarget = null
      return this.clear()
    }

    for (i = offsets.length; i--;) {
      activeTarget != targets[i]
        && scrollTop >= offsets[i]
        && (offsets[i + 1] === undefined || scrollTop < offsets[i + 1])
        && this.activate(targets[i])
    }
  }

  ScrollSpy.prototype.activate = function (target) {
    this.activeTarget = target

    this.clear()

    var selector = this.selector +
      '[data-target="' + target + '"],' +
      this.selector + '[href="' + target + '"]'

    var active = $(selector)
      .parents('li')
      .addClass('active')

    if (active.parent('.dropdown-menu').length) {
      active = active
        .closest('li.dropdown')
        .addClass('active')
    }

    active.trigger('activate.bs.scrollspy')
  }

  ScrollSpy.prototype.clear = function () {
    $(this.selector)
      .parentsUntil(this.options.target, '.active')
      .removeClass('active')
  }


  // SCROLLSPY PLUGIN DEFINITION
  // ===========================

  function Plugin(option) {
    return this.each(function () {
      var $this   = $(this)
      var data    = $this.data('bs.scrollspy')
      var options = typeof option == 'object' && option

      if (!data) $this.data('bs.scrollspy', (data = new ScrollSpy(this, options)))
      if (typeof option == 'string') data[option]()
    })
  }

  var old = $.fn.scrollspy

  $.fn.scrollspy             = Plugin
  $.fn.scrollspy.Constructor = ScrollSpy


  // SCROLLSPY NO CONFLICT
  // =====================

  $.fn.scrollspy.noConflict = function () {
    $.fn.scrollspy = old
    return this
  }


  // SCROLLSPY DATA-API
  // ==================

  $(window).on('load.bs.scrollspy.data-api', function () {
    $('[data-spy="scroll"]').each(function () {
      var $spy = $(this)
      Plugin.call($spy, $spy.data())
    })
  })

}(jQuery);

/* ========================================================================
 * Bootstrap: tab.js v3.3.7
 * http://getbootstrap.com/javascript/#tabs
 * ========================================================================
 * Copyright 2011-2016 Twitter, Inc.
 * Licensed under MIT (https://github.com/twbs/bootstrap/blob/master/LICENSE)
 * ======================================================================== */


+function ($) {
  'use strict';

  // TAB CLASS DEFINITION
  // ====================

  var Tab = function (element) {
    // jscs:disable requireDollarBeforejQueryAssignment
    this.element = $(element)
    // jscs:enable requireDollarBeforejQueryAssignment
  }

  Tab.VERSION = '3.3.7'

  Tab.TRANSITION_DURATION = 150

  Tab.prototype.show = function () {
    var $this    = this.element
    var $ul      = $this.closest('ul:not(.dropdown-menu)')
    var selector = $this.data('target')

    if (!selector) {
      selector = $this.attr('href')
      selector = selector && selector.replace(/.*(?=#[^\s]*$)/, '') // strip for ie7
    }

    if ($this.parent('li').hasClass('active')) return

    var $previous = $ul.find('.active:last a')
    var hideEvent = $.Event('hide.bs.tab', {
      relatedTarget: $this[0]
    })
    var showEvent = $.Event('show.bs.tab', {
      relatedTarget: $previous[0]
    })

    $previous.trigger(hideEvent)
    $this.trigger(showEvent)

    if (showEvent.isDefaultPrevented() || hideEvent.isDefaultPrevented()) return

    var $target = $(selector)

    this.activate($this.closest('li'), $ul)
    this.activate($target, $target.parent(), function () {
      $previous.trigger({
        type: 'hidden.bs.tab',
        relatedTarget: $this[0]
      })
      $this.trigger({
        type: 'shown.bs.tab',
        relatedTarget: $previous[0]
      })
    })
  }

  Tab.prototype.activate = function (element, container, callback) {
    var $active    = container.find('> .active')
    var transition = callback
      && $.support.transition
      && ($active.length && $active.hasClass('fade') || !!container.find('> .fade').length)

    function next() {
      $active
        .removeClass('active')
        .find('> .dropdown-menu > .active')
          .removeClass('active')
        .end()
        .find('[data-toggle="tab"]')
          .attr('aria-expanded', false)

      element
        .addClass('active')
        .find('[data-toggle="tab"]')
          .attr('aria-expanded', true)

      if (transition) {
        element[0].offsetWidth // reflow for transition
        element.addClass('in')
      } else {
        element.removeClass('fade')
      }

      if (element.parent('.dropdown-menu').length) {
        element
          .closest('li.dropdown')
            .addClass('active')
          .end()
          .find('[data-toggle="tab"]')
            .attr('aria-expanded', true)
      }

      callback && callback()
    }

    $active.length && transition ?
      $active
        .one('bsTransitionEnd', next)
        .emulateTransitionEnd(Tab.TRANSITION_DURATION) :
      next()

    $active.removeClass('in')
  }


  // TAB PLUGIN DEFINITION
  // =====================

  function Plugin(option) {
    return this.each(function () {
      var $this = $(this)
      var data  = $this.data('bs.tab')

      if (!data) $this.data('bs.tab', (data = new Tab(this)))
      if (typeof option == 'string') data[option]()
    })
  }

  var old = $.fn.tab

  $.fn.tab             = Plugin
  $.fn.tab.Constructor = Tab


  // TAB NO CONFLICT
  // ===============

  $.fn.tab.noConflict = function () {
    $.fn.tab = old
    return this
  }


  // TAB DATA-API
  // ============

  var clickHandler = function (e) {
    e.preventDefault()
    Plugin.call($(this), 'show')
  }

  $(document)
    .on('click.bs.tab.data-api', '[data-toggle="tab"]', clickHandler)
    .on('click.bs.tab.data-api', '[data-toggle="pill"]', clickHandler)

}(jQuery);

/* ========================================================================
 * Bootstrap: affix.js v3.3.7
 * http://getbootstrap.com/javascript/#affix
 * ========================================================================
 * Copyright 2011-2016 Twitter, Inc.
 * Licensed under MIT (https://github.com/twbs/bootstrap/blob/master/LICENSE)
 * ======================================================================== */


+function ($) {
  'use strict';

  // AFFIX CLASS DEFINITION
  // ======================

  var Affix = function (element, options) {
    this.options = $.extend({}, Affix.DEFAULTS, options)

    this.$target = $(this.options.target)
      .on('scroll.bs.affix.data-api', $.proxy(this.checkPosition, this))
      .on('click.bs.affix.data-api',  $.proxy(this.checkPositionWithEventLoop, this))

    this.$element     = $(element)
    this.affixed      = null
    this.unpin        = null
    this.pinnedOffset = null

    this.checkPosition()
  }

  Affix.VERSION  = '3.3.7'

  Affix.RESET    = 'affix affix-top affix-bottom'

  Affix.DEFAULTS = {
    offset: 0,
    target: window
  }

  Affix.prototype.getState = function (scrollHeight, height, offsetTop, offsetBottom) {
    var scrollTop    = this.$target.scrollTop()
    var position     = this.$element.offset()
    var targetHeight = this.$target.height()

    if (offsetTop != null && this.affixed == 'top') return scrollTop < offsetTop ? 'top' : false

    if (this.affixed == 'bottom') {
      if (offsetTop != null) return (scrollTop + this.unpin <= position.top) ? false : 'bottom'
      return (scrollTop + targetHeight <= scrollHeight - offsetBottom) ? false : 'bottom'
    }

    var initializing   = this.affixed == null
    var colliderTop    = initializing ? scrollTop : position.top
    var colliderHeight = initializing ? targetHeight : height

    if (offsetTop != null && scrollTop <= offsetTop) return 'top'
    if (offsetBottom != null && (colliderTop + colliderHeight >= scrollHeight - offsetBottom)) return 'bottom'

    return false
  }

  Affix.prototype.getPinnedOffset = function () {
    if (this.pinnedOffset) return this.pinnedOffset
    this.$element.removeClass(Affix.RESET).addClass('affix')
    var scrollTop = this.$target.scrollTop()
    var position  = this.$element.offset()
    return (this.pinnedOffset = position.top - scrollTop)
  }

  Affix.prototype.checkPositionWithEventLoop = function () {
    setTimeout($.proxy(this.checkPosition, this), 1)
  }

  Affix.prototype.checkPosition = function () {
    if (!this.$element.is(':visible')) return

    var height       = this.$element.height()
    var offset       = this.options.offset
    var offsetTop    = offset.top
    var offsetBottom = offset.bottom
    var scrollHeight = Math.max($(document).height(), $(document.body).height())

    if (typeof offset != 'object')         offsetBottom = offsetTop = offset
    if (typeof offsetTop == 'function')    offsetTop    = offset.top(this.$element)
    if (typeof offsetBottom == 'function') offsetBottom = offset.bottom(this.$element)

    var affix = this.getState(scrollHeight, height, offsetTop, offsetBottom)

    if (this.affixed != affix) {
      if (this.unpin != null) this.$element.css('top', '')

      var affixType = 'affix' + (affix ? '-' + affix : '')
      var e         = $.Event(affixType + '.bs.affix')

      this.$element.trigger(e)

      if (e.isDefaultPrevented()) return

      this.affixed = affix
      this.unpin = affix == 'bottom' ? this.getPinnedOffset() : null

      this.$element
        .removeClass(Affix.RESET)
        .addClass(affixType)
        .trigger(affixType.replace('affix', 'affixed') + '.bs.affix')
    }

    if (affix == 'bottom') {
      this.$element.offset({
        top: scrollHeight - height - offsetBottom
      })
    }
  }


  // AFFIX PLUGIN DEFINITION
  // =======================

  function Plugin(option) {
    return this.each(function () {
      var $this   = $(this)
      var data    = $this.data('bs.affix')
      var options = typeof option == 'object' && option

      if (!data) $this.data('bs.affix', (data = new Affix(this, options)))
      if (typeof option == 'string') data[option]()
    })
  }

  var old = $.fn.affix

  $.fn.affix             = Plugin
  $.fn.affix.Constructor = Affix


  // AFFIX NO CONFLICT
  // =================

  $.fn.affix.noConflict = function () {
    $.fn.affix = old
    return this
  }


  // AFFIX DATA-API
  // ==============

  $(window).on('load', function () {
    $('[data-spy="affix"]').each(function () {
      var $spy = $(this)
      var data = $spy.data()

      data.offset = data.offset || {}

      if (data.offsetBottom != null) data.offset.bottom = data.offsetBottom
      if (data.offsetTop    != null) data.offset.top    = data.offsetTop

      Plugin.call($spy, data)
    })
  })

}(jQuery);

/*!
 * Fuel UX v3.16.7 
 * Copyright 2012-2018 ExactTarget
 * Licensed under the BSD-3-Clause license (https://github.com/ExactTarget/fuelux/blob/master/LICENSE)
 */


// For more information on UMD visit: https://github.com/umdjs/umd/
( function( factory ) {
	if ( typeof define === 'function' && define.amd ) {
		define( [ 'jquery', 'bootstrap' ], factory );
	} else {
		factory( jQuery );
	}
}( function( jQuery ) {

	if ( typeof jQuery === 'undefined' ) {
		throw new Error( 'Fuel UX\'s JavaScript requires jQuery' )
	}

	if ( typeof jQuery.fn.dropdown === 'undefined' || typeof jQuery.fn.collapse === 'undefined' ) {
		throw new Error( 'Fuel UX\'s JavaScript requires Bootstrap' )
	}

	( function( $ ) {

		/* global jQuery:true */

		/*
		 * Fuel UX Checkbox
		 * https://github.com/ExactTarget/fuelux
		 *
		 * Copyright (c) 2014 ExactTarget
		 * Licensed under the BSD New license.
		 */



		// -- BEGIN MODULE CODE HERE --

		var old = $.fn.checkbox;

		// CHECKBOX CONSTRUCTOR AND PROTOTYPE

		var logError = function logError( error ) {
			if ( window && window.console && window.console.error ) {
				window.console.error( error );
			}
		};

		var Checkbox = function Checkbox( element, options ) {
			this.options = $.extend( {}, $.fn.checkbox.defaults, options );
			var $element = $( element );

			if ( element.tagName.toLowerCase() !== 'label' ) {
				logError( 'Checkbox must be initialized on the `label` that wraps the `input` element. See https://github.com/ExactTarget/fuelux/blob/master/reference/markup/checkbox.html for example of proper markup. Call `.checkbox()` on the `<label>` not the `<input>`' );
				return;
			}

			// cache elements
			this.$label = $element;
			this.$chk = this.$label.find( 'input[type="checkbox"]' );
			this.$container = $element.parent( '.checkbox' ); // the container div

			if ( !this.options.ignoreVisibilityCheck && this.$chk.css( 'visibility' ).match( /hidden|collapse/ ) ) {
				logError( 'For accessibility reasons, in order for tab and space to function on checkbox, checkbox `<input />`\'s `visibility` must not be set to `hidden` or `collapse`. See https://github.com/ExactTarget/fuelux/pull/1996 for more details.' );
			}

			// determine if a toggle container is specified
			var containerSelector = this.$chk.attr( 'data-toggle' );
			this.$toggleContainer = $( containerSelector );

			// handle internal events
			this.$chk.on( 'change', $.proxy( this.itemchecked, this ) );

			// set default state
			this.setInitialState();
		};

		Checkbox.prototype = {

			constructor: Checkbox,

			setInitialState: function setInitialState() {
				var $chk = this.$chk;

				// get current state of input
				var checked = $chk.prop( 'checked' );
				var disabled = $chk.prop( 'disabled' );

				// sync label class with input state
				this.setCheckedState( $chk, checked );
				this.setDisabledState( $chk, disabled );
			},

			setCheckedState: function setCheckedState( element, checked ) {
				var $chk = element;
				var $lbl = this.$label;
				var $containerToggle = this.$toggleContainer;

				if ( checked ) {
					$chk.prop( 'checked', true );
					$lbl.addClass( 'checked' );
					$containerToggle.removeClass( 'hide hidden' );
					$lbl.trigger( 'checked.fu.checkbox' );
				} else {
					$chk.prop( 'checked', false );
					$lbl.removeClass( 'checked' );
					$containerToggle.addClass( 'hidden' );
					$lbl.trigger( 'unchecked.fu.checkbox' );
				}

				$lbl.trigger( 'changed.fu.checkbox', checked );
			},

			setDisabledState: function setDisabledState( element, disabled ) {
				var $chk = $( element );
				var $lbl = this.$label;

				if ( disabled ) {
					$chk.prop( 'disabled', true );
					$lbl.addClass( 'disabled' );
					$lbl.trigger( 'disabled.fu.checkbox' );
				} else {
					$chk.prop( 'disabled', false );
					$lbl.removeClass( 'disabled' );
					$lbl.trigger( 'enabled.fu.checkbox' );
				}

				return $chk;
			},

			itemchecked: function itemchecked( evt ) {
				var $chk = $( evt.target );
				var checked = $chk.prop( 'checked' );

				this.setCheckedState( $chk, checked );
			},

			toggle: function toggle() {
				var checked = this.isChecked();

				if ( checked ) {
					this.uncheck();
				} else {
					this.check();
				}
			},

			check: function check() {
				this.setCheckedState( this.$chk, true );
			},

			uncheck: function uncheck() {
				this.setCheckedState( this.$chk, false );
			},

			isChecked: function isChecked() {
				var checked = this.$chk.prop( 'checked' );
				return checked;
			},

			enable: function enable() {
				this.setDisabledState( this.$chk, false );
			},

			disable: function disable() {
				this.setDisabledState( this.$chk, true );
			},

			destroy: function destroy() {
				this.$label.remove();
				return this.$label[ 0 ].outerHTML;
			}
		};

		Checkbox.prototype.getValue = Checkbox.prototype.isChecked;

		// CHECKBOX PLUGIN DEFINITION

		$.fn.checkbox = function checkbox( option ) {
			var args = Array.prototype.slice.call( arguments, 1 );
			var methodReturn;

			var $set = this.each( function applyData() {
				var $this = $( this );
				var data = $this.data( 'fu.checkbox' );
				var options = typeof option === 'object' && option;

				if ( !data ) {
					$this.data( 'fu.checkbox', ( data = new Checkbox( this, options ) ) );
				}

				if ( typeof option === 'string' ) {
					methodReturn = data[ option ].apply( data, args );
				}
			} );

			return ( methodReturn === undefined ) ? $set : methodReturn;
		};

		$.fn.checkbox.defaults = {
			ignoreVisibilityCheck: false
		};

		$.fn.checkbox.Constructor = Checkbox;

		$.fn.checkbox.noConflict = function noConflict() {
			$.fn.checkbox = old;
			return this;
		};

		// DATA-API

		$( document ).on( 'mouseover.fu.checkbox.data-api', '[data-initialize=checkbox]', function initializeCheckboxes( e ) {
			var $control = $( e.target );
			if ( !$control.data( 'fu.checkbox' ) ) {
				$control.checkbox( $control.data() );
			}
		} );

		// Must be domReady for AMD compatibility
		$( function onReadyInitializeCheckboxes() {
			$( '[data-initialize=checkbox]' ).each( function initializeCheckbox() {
				var $this = $( this );
				if ( !$this.data( 'fu.checkbox' ) ) {
					$this.checkbox( $this.data() );
				}
			} );
		} );



	} )( jQuery );


	( function( $ ) {

		/* global jQuery:true */

		/*
		 * Fuel UX Combobox
		 * https://github.com/ExactTarget/fuelux
		 *
		 * Copyright (c) 2014 ExactTarget
		 * Licensed under the BSD New license.
		 */



		// -- BEGIN MODULE CODE HERE --

		var old = $.fn.combobox;


		// COMBOBOX CONSTRUCTOR AND PROTOTYPE

		var Combobox = function( element, options ) {
			this.$element = $( element );
			this.options = $.extend( {}, $.fn.combobox.defaults, options );

			this.$dropMenu = this.$element.find( '.dropdown-menu' );
			this.$input = this.$element.find( 'input' );
			this.$button = this.$element.find( '.btn' );
			this.$inputGroupBtn = this.$element.find( '.input-group-btn' );

			this.$element.on( 'click.fu.combobox', 'a', $.proxy( this.itemclicked, this ) );
			this.$element.on( 'change.fu.combobox', 'input', $.proxy( this.inputchanged, this ) );
			this.$element.on( 'shown.bs.dropdown', $.proxy( this.menuShown, this ) );
			this.$input.on( 'keyup.fu.combobox', $.proxy( this.keypress, this ) );

			// set default selection
			this.setDefaultSelection();

			// if dropdown is empty, disable it
			var items = this.$dropMenu.children( 'li' );
			if ( items.length === 0 ) {
				this.$button.addClass( 'disabled' );
			}

			// filter on load in case the first thing they do is press navigational key to pop open the menu
			if ( this.options.filterOnKeypress ) {
				this.options.filter( this.$dropMenu.find( 'li' ), this.$input.val(), this );
			}

		};

		Combobox.prototype = {

			constructor: Combobox,

			destroy: function() {
				this.$element.remove();
				// remove any external bindings
				// [none]

				// set input value attrbute in markup
				this.$element.find( 'input' ).each( function() {
					$( this ).attr( 'value', $( this ).val() );
				} );

				// empty elements to return to original markup
				// [none]

				return this.$element[ 0 ].outerHTML;
			},

			doSelect: function( $item ) {

				if ( typeof $item[ 0 ] !== 'undefined' ) {
					// remove selection from old item, may result in remove and
					// re-addition of class if item is the same
					this.$element.find( 'li.selected:first' ).removeClass( 'selected' );

					// add selection to new item
					this.$selectedItem = $item;
					this.$selectedItem.addClass( 'selected' );

					// update input
					this.$input.val( this.$selectedItem.text().trim() );
				} else {
					// this is a custom input, not in the menu
					this.$selectedItem = null;
					this.$element.find( 'li.selected:first' ).removeClass( 'selected' );
				}
			},

			clearSelection: function() {
				this.$selectedItem = null;
				this.$input.val( '' );
				this.$dropMenu.find( 'li' ).removeClass( 'selected' );
			},

			menuShown: function() {
				if ( this.options.autoResizeMenu ) {
					this.resizeMenu();
				}
			},

			resizeMenu: function() {
				var width = this.$element.outerWidth();
				this.$dropMenu.outerWidth( width );
			},

			selectedItem: function() {
				var item = this.$selectedItem;
				var data = {};

				if ( item ) {
					var txt = this.$selectedItem.text().trim();
					data = $.extend( {
						text: txt
					}, this.$selectedItem.data() );
				} else {
					data = {
						text: this.$input.val().trim(),
						notFound: true
					};
				}

				return data;
			},

			selectByText: function( text ) {
				var $item = $( [] );
				this.$element.find( 'li' ).each( function() {
					if ( ( this.textContent || this.innerText || $( this ).text() || '' ).trim().toLowerCase() === ( text || '' ).trim().toLowerCase() ) {
						$item = $( this );
						return false;
					}
				} );

				this.doSelect( $item );
			},

			selectByValue: function( value ) {
				var selector = 'li[data-value="' + value + '"]';
				this.selectBySelector( selector );
			},

			selectByIndex: function( index ) {
				// zero-based index
				var selector = 'li:eq(' + index + ')';
				this.selectBySelector( selector );
			},

			selectBySelector: function( selector ) {
				var $item = this.$element.find( selector );
				this.doSelect( $item );
			},

			setDefaultSelection: function() {
				var selector = 'li[data-selected=true]:first';
				var item = this.$element.find( selector );

				if ( item.length > 0 ) {
					// select by data-attribute
					this.selectBySelector( selector );
					item.removeData( 'selected' );
					item.removeAttr( 'data-selected' );
				}
			},

			enable: function() {
				this.$element.removeClass( 'disabled' );
				this.$input.removeAttr( 'disabled' );
				this.$button.removeClass( 'disabled' );
			},

			disable: function() {
				this.$element.addClass( 'disabled' );
				this.$input.attr( 'disabled', true );
				this.$button.addClass( 'disabled' );
			},

			itemclicked: function( e ) {
				this.$selectedItem = $( e.target ).parent();

				// set input text and trigger input change event marked as synthetic
				this.$input.val( this.$selectedItem.text().trim() ).trigger( 'change', {
					synthetic: true
				} );

				// pass object including text and any data-attributes
				// to onchange event
				var data = this.selectedItem();

				// trigger changed event
				this.$element.trigger( 'changed.fu.combobox', data );

				e.preventDefault();

				// return focus to control after selecting an option
				this.$element.find( '.dropdown-toggle' ).focus();
			},

			keypress: function( e ) {
				var ENTER = 13;
				//var TAB = 9;
				var ESC = 27;
				var LEFT = 37;
				var UP = 38;
				var RIGHT = 39;
				var DOWN = 40;

				var IS_NAVIGATIONAL = (
					e.which === UP ||
					e.which === DOWN ||
					e.which === LEFT ||
					e.which === RIGHT
				);

				if ( this.options.showOptionsOnKeypress && !this.$inputGroupBtn.hasClass( 'open' ) ) {
					this.$button.dropdown( 'toggle' );
					this.$input.focus();
				}

				if ( e.which === ENTER ) {
					e.preventDefault();

					var selected = this.$dropMenu.find( 'li.selected' ).text().trim();
					if ( selected.length > 0 ) {
						this.selectByText( selected );
					} else {
						this.selectByText( this.$input.val() );
					}

					this.$inputGroupBtn.removeClass( 'open' );
				} else if ( e.which === ESC ) {
					e.preventDefault();
					this.clearSelection();
					this.$inputGroupBtn.removeClass( 'open' );
				} else if ( this.options.showOptionsOnKeypress ) {
					if ( e.which === DOWN || e.which === UP ) {
						e.preventDefault();
						var $selected = this.$dropMenu.find( 'li.selected' );
						if ( $selected.length > 0 ) {
							if ( e.which === DOWN ) {
								$selected = $selected.next( ':not(.hidden)' );
							} else {
								$selected = $selected.prev( ':not(.hidden)' );
							}
						}

						if ( $selected.length === 0 ) {
							if ( e.which === DOWN ) {
								$selected = this.$dropMenu.find( 'li:not(.hidden):first' );
							} else {
								$selected = this.$dropMenu.find( 'li:not(.hidden):last' );
							}
						}
						this.doSelect( $selected );
					}
				}

				// Avoid filtering on navigation key presses
				if ( this.options.filterOnKeypress && !IS_NAVIGATIONAL ) {
					this.options.filter( this.$dropMenu.find( 'li' ), this.$input.val(), this );
				}

				this.previousKeyPress = e.which;
			},

			inputchanged: function( e, extra ) {
				var val = $( e.target ).val();
				// skip processing for internally-generated synthetic event
				// to avoid double processing
				if ( extra && extra.synthetic ) {
					this.selectByText( val );
					return;
				}
				this.selectByText( val );

				// find match based on input
				// if no match, pass the input value
				var data = this.selectedItem();
				if ( data.text.length === 0 ) {
					data = {
						text: val
					};
				}

				// trigger changed event
				this.$element.trigger( 'changed.fu.combobox', data );
			}
		};

		Combobox.prototype.getValue = Combobox.prototype.selectedItem;

		// COMBOBOX PLUGIN DEFINITION

		$.fn.combobox = function( option ) {
			var args = Array.prototype.slice.call( arguments, 1 );
			var methodReturn;

			var $set = this.each( function() {
				var $this = $( this );
				var data = $this.data( 'fu.combobox' );
				var options = typeof option === 'object' && option;

				if ( !data ) {
					$this.data( 'fu.combobox', ( data = new Combobox( this, options ) ) );
				}

				if ( typeof option === 'string' ) {
					methodReturn = data[ option ].apply( data, args );
				}
			} );

			return ( methodReturn === undefined ) ? $set : methodReturn;
		};

		$.fn.combobox.defaults = {
			autoResizeMenu: true,
			filterOnKeypress: false,
			showOptionsOnKeypress: false,
			filter: function filter( list, predicate, self ) {
				var visible = 0;
				self.$dropMenu.find( '.empty-indicator' ).remove();

				list.each( function( i ) {
					var $li = $( this );
					var text = $( this ).text().trim();

					$li.removeClass();

					if ( text === predicate ) {
						$li.addClass( 'text-success' );
						visible++;
					} else if ( text.substr( 0, predicate.length ) === predicate ) {
						$li.addClass( 'text-info' );
						visible++;
					} else {
						$li.addClass( 'hidden' );
					}
				} );

				if ( visible === 0 ) {
					self.$dropMenu.append( '<li class="empty-indicator text-muted"><em>No Matches</em></li>' );
				}
			}
		};

		$.fn.combobox.Constructor = Combobox;

		$.fn.combobox.noConflict = function() {
			$.fn.combobox = old;
			return this;
		};

		// DATA-API

		$( document ).on( 'mousedown.fu.combobox.data-api', '[data-initialize=combobox]', function( e ) {
			var $control = $( e.target ).closest( '.combobox' );
			if ( !$control.data( 'fu.combobox' ) ) {
				$control.combobox( $control.data() );
			}
		} );

		// Must be domReady for AMD compatibility
		$( function() {
			$( '[data-initialize=combobox]' ).each( function() {
				var $this = $( this );
				if ( !$this.data( 'fu.combobox' ) ) {
					$this.combobox( $this.data() );
				}
			} );
		} );



	} )( jQuery );


	( function( $ ) {
		/* global jQuery:true */

		/*
		 * Jaws Date calendar converter
		 *
		 */

		function dateJalali(args) {
			var leapYears = [1, 5, 9, 13, 17, 22, 26, 30];
			var gMonthDays = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
			var jMonthDays = [31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29];

			var year = 0,
				month = 0,
				day = 0,
				wday = 0,
				days = 0;

			switch ($.type(args)) {
				case 'string':
					args = args.split(/\/|\-/);
					args[1]--;
					// without break - goto array

				case 'array':
					if (args.length) {
						year  = parseInt(args[0]);
						month = parseInt(args[1]) + 1;
						day   = parseInt(args[2]);

						// calculating Jalali calendar total days
						var ym = Math.floor((month-1)/12);
						month = month - ym*12;
						year  = year + ym - 1;
						days =  365*year + Math.floor(year/33)*8 + Math.floor(((year%33)+3)/4);
						for (var i=0; i < (month-1); ++i) {
							days += jMonthDays[i];
						}
						days = days + day;

						break;
					}

					args = null;
					// without break - goto number

				case 'number':
					if (isNaN(args)) {
						return new Date(NaN);
					}

					var gdate = new Date();
					if (args != null) {
						gdate.setTime(args*1000);
					}

					year  = parseInt(gdate.getFullYear());
					month = parseInt(Number(gdate.getMonth()) + 1);
					day   = parseInt(gdate.getDate());

					year--;
					days = 365*year + Math.floor(year/4) - Math.floor(year/100) + Math.floor(year/400);
					year++;
					for (var i=0; i < (month-1); ++i) {
						days += gMonthDays[i];
					}
					// is leap year
					if (month > 2 && ((year%4) == 0 && ((year%100) != 0 || (year%400) == 0))) {
						days++;
					}
					days = days + day - 226894;

					break;

				default:
					return new Date(NaN);
			}

			wday = (days + 4) % 7;
			year = Math.floor(days/12053)*33; // 12053 = 33*365 + 8
			days %= 12053;
			days--;
			year = year + Math.floor(days / 1461)*4; // 1461 = 4*365 + 1
			days  = (days % 1461) + 1;

			year++;
			var isLeap = leapYears.indexOf((year % 33)) > -1;
			while (days > (365 + isLeap)) {
				days = days - (365 + isLeap);
				year++;
				isLeap = leapYears.indexOf((year % 33)) > -1;
			}

			month = 0;
			while (days > (jMonthDays[month] + ((month==11)? isLeap : 0)))
			{
				days -= jMonthDays[month];
				month++;
			}

			this.jYear  = year;
			this.jMonth = month;
			this.jDay   = days;
			this.jWeekDay = wday;

			// check is given date in a leap year
			this.isLeapYear = leapYears.indexOf((this.jYear % 33)) > -1;

			this.getFullYear = function() {
				return this.jYear;
			}

			this.getMonth = function() {
				return this.jMonth;
			}

			this.getDate = function() {
				return this.jDay;
			}

			this.getDay = function() {
				return this.jWeekDay;
			}

			this.getMonthDays = function(month) {
				return (this.isLeapYear && month == 12)? 30 : jMonthDays[month-1];
			}

			this.format = function(format) {
				var i = 0;
				var result = '';
				format = format? format : 'yyyy-mm-dd';
				while (i < format.length) {
					switch (format.charAt(i)) {
						case 'd':
							if (format.substr(i, 2) == 'dd') {
								i++;
								result += this.jDay;
							} else {
								result += this.jDay;
							}

							break;

						case 'm':
							if (format.substr(i, 2) == 'mm') {
								i++;
								result += this.jMonth + 1;
							} else {
								result += this.jMonth + 1;
							}
							break;

						case 'y':
							if (format.substr(i, 4) == 'yyyy') {
								i+= 3;
								result += this.jYear;
							} else if(format.substr(i, 3) == 'yyy') {
								i+= 2;
								result += this.jYear;
							} else if(format.substr(i, 2) == 'yy') {
								i++;
								result += this.jYear;
							} else {
								result += this.jYear;
							}
							break;

						default:
							result += format.charAt(i);
							break;
					}

					i++;
				}
				return result;
			}

			this.toString = function() {
				return this.jYear + '/' + (this.jMonth + 1) + '/' + this.jDay;
			}
		}

		function dateGregorian(args) {
			this.gdate;
			var gMonthDays = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];

			switch ($.type(args)) {
				case 'number':
					if (isNaN(args)) {
						return new Date(NaN);
					}

					this.gdate = new Date();
					this.gdate.setTime(args*1000);
					break;

				case 'string':
					args = args.split(/\/|\-/);
					args[1]--;
					// without break - goto array

				case 'array':
					if (args.length) {
						this.gdate = new Date(args[0], args[1], args[2]);
					} else {
						this.gdate = new Date();
					}
					break;

				default:
					return new Date(NaN);
			}

			this.gYear  = parseInt(this.gdate.getFullYear());
			this.gMonth = parseInt(this.gdate.getMonth());
			this.gDay   = parseInt(this.gdate.getDate());
			this.gWeekDay = parseInt(this.gdate.getDay());
			// check is given date in a leap year
			this.isLeapYear = ((this.gYear%4) == 0 && ((this.gYear%100) != 0 || (this.gYear%400) == 0));

			this.getFullYear = function() {
				return this.gYear;
			}

			this.getMonth = function() {
				return this.gMonth;
			}

			this.getDate = function() {
				return this.gDay;
			}

			this.getDay = function() {
				return this.gWeekDay;
			}

			this.getMonthDays = function(month) {
				return (this.isLeapYear && month == 2)? 29 : gMonthDays[month-1];
			}

			this.format = function(format) {
				var i = 0;
				var result = '';
				format = format? format : 'yyyy-mm-dd';
				while (i < format.length) {
					switch (format.charAt(i)) {
						case 'd':
							if (format.substr(i, 2) == 'dd') {
								i++;
								result += this.gDay;
							} else {
								result += this.gDay;
							}

							break;

						case 'm':
							if (format.substr(i, 2) == 'mm') {
								i++;
								result += this.gMonth + 1;
							} else {
								result += this.gMonth + 1;
							}
							break;

						case 'y':
							if (format.substr(i, 4) == 'yyyy') {
								i+= 3;
								result += this.gYear;
							} else if(format.substr(i, 3) == 'yyy') {
								i+= 2;
								result += this.gYear;
							} else if(format.substr(i, 2) == 'yy') {
								i++;
								result += this.gYear;
							} else {
								result += this.gYear;
							}
							break;

						default:
							result += format.charAt(i);
							break;
					}

					i++;
				}

				return result;
			}
		}

		$.dateCalendar = function(calendar, args) {
			switch (calendar) {
				case 'jalali':
				case 'Jalali':
					return new dateJalali(args);
					break;

				default:
					// Gregorian
					return new dateGregorian(args);
			}
		}
	} )( jQuery );


	( function( $ ) {

		/* global jQuery:true */

		/*
		 * Fuel UX Datepicker
		 * https://github.com/ExactTarget/fuelux
		 *
		 * Copyright (c) 2014 ExactTarget
		 * Licensed under the BSD New license.
		 */



		// -- BEGIN MODULE CODE HERE --

		var INVALID_DATE = 'Invalid Date';
		var MOMENT_NOT_AVAILABLE = 'moment.js is not available so you cannot use this function';

		var datepickerStack = [];
		var moment = false;
		var old = $.fn.datepicker;
		var requestedMoment = false;

		var runStack = function() {
			var i, l;
			requestedMoment = true;
			for ( i = 0, l = datepickerStack.length; i < l; i++ ) {
				datepickerStack[ i ].init.call( datepickerStack[ i ].scope );
			}
			datepickerStack = [];
		};

		//only load moment if it's there. otherwise we'll look for it in window.moment
		if ( typeof define === 'function' && define.amd ) { //check if AMD is available
			require( [ 'moment' ], function( amdMoment ) {
				moment = amdMoment;
				runStack();
			}, function( err ) {
				var failedId = err.requireModules && err.requireModules[ 0 ];
				if ( failedId === 'moment' ) {
					runStack();
				}
			} );
		} else {
			runStack();
		}

		// DATEPICKER CONSTRUCTOR AND PROTOTYPE

		var Datepicker = function( element, options ) {
			this.$element = $( element );
			this.options = $.extend( true, {}, $.fn.datepicker.defaults, options );

			this.$calendar = this.$element.find( '.datepicker-calendar' );
			this.$days = this.$calendar.find( '.datepicker-calendar-days' );
			this.$header = this.$calendar.find( '.datepicker-calendar-header' );
			this.$headerTitle = this.$header.find( '.title' );
			this.$input = this.$element.find( 'input' );
			this.$inputGroupBtn = this.$element.find( '.input-group-btn' );
			this.$wheels = this.$element.find( '.datepicker-wheels' );
			this.$wheelsMonth = this.$element.find( '.datepicker-wheels-month' );
			this.$wheelsYear = this.$element.find( '.datepicker-wheels-year' );

			this.artificialScrolling = false;
			this.options.date = this.dateCalendar(NaN);
			this.formatDate = this.options.formatDate || this.formatDate;
			this.inputValue = null;
			this.moment = false;
			this.momentFormat = null;
			this.parseDate = this.options.parseDate || this.parseDate;
			this.preventBlurHide = false;
			this.restricted = this.options.restricted || [];
			this.restrictedParsed = [];
			this.restrictedText = this.options.restrictedText;
			this.sameYearOnly = this.options.sameYearOnly;
			this.selectedDate = null;
			this.yearRestriction = null;

			this.$calendar.find( '.datepicker-today' ).on( 'click.fu.datepicker', $.proxy( this.todayClicked, this ) );
			this.$days.on( 'click.fu.datepicker', 'tr td button', $.proxy( this.dateClicked, this ) );
			this.$header.find( '.next' ).on( 'click.fu.datepicker', $.proxy( this.next, this ) );
			this.$header.find( '.prev' ).on( 'click.fu.datepicker', $.proxy( this.prev, this ) );
			this.$headerTitle.on( 'click.fu.datepicker', $.proxy( this.titleClicked, this ) );
			this.$input.on( 'change.fu.datepicker', $.proxy( this.inputChanged, this ) );
			this.$input.on( 'mousedown.fu.datepicker', $.proxy( this.showDropdown, this ) );
			this.$inputGroupBtn.on( 'hidden.bs.dropdown', $.proxy( this.hide, this ) );
			this.$inputGroupBtn.on( 'shown.bs.dropdown', $.proxy( this.show, this ) );
			this.$wheels.find( '.datepicker-wheels-back' ).on( 'click.fu.datepicker', $.proxy( this.backClicked, this ) );
			this.$wheels.find( '.datepicker-wheels-select' ).on( 'click.fu.datepicker', $.proxy( this.selectClicked, this ) );
			this.$wheelsMonth.on( 'click.fu.datepicker', 'ul button', $.proxy( this.monthClicked, this ) );
			this.$wheelsYear.on( 'click.fu.datepicker', 'ul button', $.proxy( this.yearClicked, this ) );
			this.$wheelsYear.find( 'ul' ).on( 'scroll.fu.datepicker', $.proxy( this.onYearScroll, this ) );

			var init = function() {
				if ( this.checkForMomentJS() ) {
					moment = moment || window.moment; // need to pull in the global moment if they didn't do it via require
					this.moment = true;
					this.momentFormat = this.options.momentConfig.format;
					this.setCulture( this.options.momentConfig.culture );

					// support moment with lang (< v2.8) or locale
					moment.locale = moment.locale || moment.lang;
				}

				this.setRestrictedDates( this.restricted );
				if ( !this.setDate( this.options.date ) ) {
					if ( !this.setDate( this.$input.val() ) ) {
						this.$input.val( '' );
						this.inputValue = this.$input.val();
					}
				}

				if ( this.sameYearOnly ) {
					this.yearRestriction = ( this.selectedDate ) ? this.selectedDate.getFullYear() : this.dateCalendar().getFullYear();
				}
			};

			if ( requestedMoment ) {
				init.call( this );
			} else {
				datepickerStack.push( {
					init: init,
					scope: this
				} );
			}
		};

		Datepicker.prototype = {

			constructor: Datepicker,

			dateCalendar: function(args) {
				args = (arguments.length == 1)? args : Array.from(arguments);
				if ($.type(args) === 'date') {
					return args;
				}

				return $.dateCalendar(this.options.calendar, args);
			},

			backClicked: function() {
				this.changeView( 'calendar' );
			},

			changeView: function( view, date ) {
				if ( view === 'wheels' ) {
					this.$calendar.hide().attr( 'aria-hidden', 'true' );
					this.$wheels.show().removeAttr( 'aria-hidden', '' );
					if ( date ) {
						this.renderWheel( date );
					}

				} else {
					this.$wheels.hide().attr( 'aria-hidden', 'true' );
					this.$calendar.show().removeAttr( 'aria-hidden', '' );
					if ( date ) {
						this.renderMonth( date );
					}

				}
			},

			checkForMomentJS: function() {
				if (
					( $.isFunction( window.moment ) || ( typeof moment !== 'undefined' && $.isFunction( moment ) ) ) &&
					$.isPlainObject( this.options.momentConfig ) &&
					( typeof this.options.momentConfig.culture === 'string' && typeof this.options.momentConfig.format === 'string' )
				) {
					return true;
				} else {
					return false;
				}
			},

			dateClicked: function( e ) {
				var $td = $( e.currentTarget ).parents( 'td:first' );
				var date;

				if ( $td.hasClass( 'restricted' ) ) {
					return;
				}

				this.$days.find( 'td.selected' ).removeClass( 'selected' );
				$td.addClass( 'selected' );

				date = this.dateCalendar( $td.attr( 'data-year' ), $td.attr( 'data-month' ), $td.attr( 'data-date' ) );
				this.selectedDate = date;
				this.$input.val( this.formatDate( date ) );
				this.inputValue = this.$input.val();
				this.hide();
				this.$input.focus();
				this.$element.trigger( 'dateClicked.fu.datepicker', date );
			},

			destroy: function() {
				this.$element.remove();
				// any external bindings
				// [none]

				// empty elements to return to original markup
				this.$days.find( 'tbody' ).empty();
				this.$wheelsYear.find( 'ul' ).empty();

				return this.$element[ 0 ].outerHTML;
			},

			disable: function() {
				this.$element.addClass( 'disabled' );
				this.$element.find( 'input, button' ).attr( 'disabled', 'disabled' );
				this.$inputGroupBtn.removeClass( 'open' );
			},

			enable: function() {
				this.$element.removeClass( 'disabled' );
				this.$element.find( 'input, button' ).removeAttr( 'disabled' );
			},

			formatDate: function( date ) {
				var padTwo = function( value ) {
					var s = '0' + value;
					return s.substr( s.length - 2 );
				};

				if ( this.moment ) {
					return moment( date ).format( this.momentFormat );
				} else {
					return date.getFullYear()  + '/' + padTwo( date.getMonth() +1) + '/' + padTwo( date.getDate() );
				}
			},

			getCulture: function() {
				if ( this.moment ) {
					return moment.locale();
				} else {
					throw MOMENT_NOT_AVAILABLE;
				}
			},

			getDate: function() {
				return ( !this.selectedDate ) ? this.dateCalendar( NaN ) : this.selectedDate;
			},

			getFormat: function() {
				if ( this.moment ) {
					return this.momentFormat;
				} else {
					throw MOMENT_NOT_AVAILABLE;
				}
			},

			getFormattedDate: function() {
				return ( !this.selectedDate ) ? INVALID_DATE : this.formatDate( this.selectedDate );
			},

			getRestrictedDates: function() {
				return this.restricted;
			},

			inputChanged: function() {
				var inputVal = this.$input.val();
				var date;
				if ( inputVal !== this.inputValue ) {
					date = this.setDate( inputVal );
					if ( date === null ) {
						this.$element.trigger( 'inputParsingFailed.fu.datepicker', inputVal );
					} else if ( date === false ) {
						this.$element.trigger( 'inputRestrictedDate.fu.datepicker', date );
					} else {
						this.$element.trigger( 'changed.fu.datepicker', date );
					}

				}
			},

			show: function() {
				var date = ( this.selectedDate ) ? this.selectedDate : this.dateCalendar();
				this.changeView( 'calendar', date );
				this.$inputGroupBtn.addClass( 'open' );
				this.$element.trigger( 'shown.fu.datepicker' );
			},

			showDropdown: function( e ) { //input mousedown handler, name retained for legacy support of showDropdown
				if ( !this.$input.is( ':focus' ) && !this.$inputGroupBtn.hasClass( 'open' ) ) {
					this.show();
				}
			},

			hide: function() {
				this.$inputGroupBtn.removeClass( 'open' );
				this.$element.trigger( 'hidden.fu.datepicker' );
			},

			hideDropdown: function() { //for legacy support of hideDropdown
				this.hide();
			},

			isInvalidDate: function( date ) {
				var dateString = date.toString();
				if ( dateString === INVALID_DATE || dateString === 'NaN' ) {
					return true;
				}

				return false;
			},

			isRestricted: function( date, month, year ) {
				var restricted = this.restrictedParsed;
				var i, from, l, to;

				if ( this.sameYearOnly && this.yearRestriction !== null && year !== this.yearRestriction ) {
					return true;
				}

				for ( i = 0, l = restricted.length; i < l; i++ ) {
					from = restricted[ i ].from;
					to = restricted[ i ].to;
					if (
						( year > from.year || ( year === from.year && month > from.month ) || ( year === from.year && month === from.month && date >= from.date ) ) &&
						( year < to.year || ( year === to.year && month < to.month ) || ( year === to.year && month === to.month && date <= to.date ) )
					) {
						return true;
					}

				}

				return false;
			},

			monthClicked: function( e ) {
				this.$wheelsMonth.find( '.selected' ).removeClass( 'selected' );
				$( e.currentTarget ).parent().addClass( 'selected' );
			},

			next: function() {
				var month = this.$headerTitle.attr( 'data-month' );
				var year = this.$headerTitle.attr( 'data-year' );
				month++;
				if ( month > 11 ) {
					if ( this.sameYearOnly ) {
						return;
					}

					month = 0;
					year++;
				}

				this.renderMonth( this.dateCalendar( year, month, 1 ) );
			},

			onYearScroll: function( e ) {
				if ( this.artificialScrolling ) {
					return;
				}

				var $yearUl = $( e.currentTarget );
				var height = ( $yearUl.css( 'box-sizing' ) === 'border-box' ) ? $yearUl.outerHeight() : $yearUl.height();
				var scrollHeight = $yearUl.get( 0 ).scrollHeight;
				var scrollTop = $yearUl.scrollTop();
				var bottomPercentage = ( height / ( scrollHeight - scrollTop ) ) * 100;
				var topPercentage = ( scrollTop / scrollHeight ) * 100;
				var i, start;

				if ( topPercentage < 5 ) {
					start = parseInt( $yearUl.find( 'li:first' ).attr( 'data-year' ), 10 );
					for ( i = ( start - 1 ); i > ( start - 11 ); i-- ) {
						$yearUl.prepend( '<li data-year="' + i + '"><button type="button">' + i + '</button></li>' );
					}
					this.artificialScrolling = true;
					$yearUl.scrollTop( ( $yearUl.get( 0 ).scrollHeight - scrollHeight ) + scrollTop );
					this.artificialScrolling = false;
				} else if ( bottomPercentage > 90 ) {
					start = parseInt( $yearUl.find( 'li:last' ).attr( 'data-year' ), 10 );
					for ( i = ( start + 1 ); i < ( start + 11 ); i++ ) {
						$yearUl.append( '<li data-year="' + i + '"><button type="button">' + i + '</button></li>' );
					}
				}
			},

			//some code ripped from http://stackoverflow.com/questions/2182246/javascript-dates-in-ie-nan-firefox-chrome-ok
			parseDate: function( date ) {
				var self = this;
				var BAD_DATE = this.dateCalendar( NaN );
				var dt, isoExp, momentParse, momentParseWithFormat, tryMomentParseAll, month, parts, use;

				if ( date ) {
					if ( this.moment ) { //if we have moment, use that to parse the dates
						momentParseWithFormat = function( d ) {
							var md = moment( d, self.momentFormat );
							return ( true === md.isValid() ) ? md.toDate() : BAD_DATE;
						};
						momentParse = function( d ) {
							var md = moment( this.dateCalendar( d ) );
							return ( true === md.isValid() ) ? md.toDate() : BAD_DATE;
						};

						tryMomentParseAll = function( rawDateString, parseFunc1, parseFunc2 ) {
							var pd = parseFunc1( rawDateString );
							if ( !self.isInvalidDate( pd ) ) {
								return pd;
							}

							pd = parseFunc2( rawDateString );
							if ( !self.isInvalidDate( pd ) ) {
								return pd;
							}

							return BAD_DATE;
						};

						if ( 'string' === typeof( date ) ) {
							// Attempts to parse date strings using this.momentFormat, falling back on newing a date
							return tryMomentParseAll( date, momentParseWithFormat, momentParse );
						} else {
							// Attempts to parse date by newing a date object directly, falling back on parsing using this.momentFormat
							return tryMomentParseAll( date, momentParse, momentParseWithFormat );
						}

					} else { //if moment isn't present, use previous date parsing strategy
						if ( typeof( date ) === 'string' ) {
							date = date.split( 'T' )[ 0 ];
							isoExp = /^\s*(\d{4})[\-|\/](\d\d)[\-|\/](\d\d)\s*$/;
							parts = isoExp.exec( date );
							if ( parts ) {
								month = parseInt( parts[ 2 ], 10 );
								return this.dateCalendar( parts[ 1 ], month - 1, parts[ 3 ] );
							}
						} else {
							dt = this.dateCalendar( date );
							if ( !this.isInvalidDate( dt ) ) {
								return dt;
							}

						}

					}

				}
				return this.dateCalendar( NaN );
			},

			prev: function() {
				var month = this.$headerTitle.attr( 'data-month' );
				var year = this.$headerTitle.attr( 'data-year' );
				month--;
				if ( month < 0 ) {
					if ( this.sameYearOnly ) {
						return;
					}

					month = 11;
					year--;
				}

				this.renderMonth( this.dateCalendar( year, month, 1 ) );
			},

			renderMonth: function( date ) {
				date = date || this.dateCalendar();

				var firstDay = this.dateCalendar( date.getFullYear(), date.getMonth(), 1 ).getDay();
				var lastDate = this.dateCalendar( date.getFullYear(), date.getMonth() + 1, 0 ).getDate();
				var lastMonthDate = this.dateCalendar( date.getFullYear(), date.getMonth(), 0 ).getDate();

				var $month = this.$headerTitle.find( '.month' );
				var month = date.getMonth();

				var now = this.dateCalendar();
				var nowDate = now.getDate();
				var nowMonth = now.getMonth();
				var nowYear = now.getFullYear();
				var selected = this.selectedDate;
				var $tbody = this.$days.find( 'tbody' );
				var year = date.getFullYear();
				var curDate, curMonth, curYear, i, j, rows, stage, previousStage, lastStage, $td, $tr;

				if ( selected ) {
					selected = {
						date: selected.getDate(),
						month: selected.getMonth(),
						year: selected.getFullYear()
					};
				}

				$month.find( '.current' ).removeClass( 'current' );
				$month.find( 'span[data-month="' + month + '"]' ).addClass( 'current' );
				this.$headerTitle.find( '.year' ).text( year );
				this.$headerTitle.attr( {
					'data-month': month,
					'data-year': year
				} );


				$tbody.empty();
				if ( firstDay !== 0 ) {
					curDate = lastMonthDate - firstDay + 1;
					stage = -1;
				} else {
					curDate = 1;
					stage = 0;
				}

				rows = ( lastDate <= ( 35 - firstDay ) ) ? 5 : 6;
				for ( i = 0; i < rows; i++ ) {
					$tr = $( '<tr></tr>' );
					for ( j = 0; j < 7; j++ ) {
						$td = $( '<td></td>' );
						if ( stage === -1 ) {
							$td.addClass( 'last-month' );
							if ( previousStage !== stage ) {
								$td.addClass( 'first' );
							}
						} else if ( stage === 1 ) {
							$td.addClass( 'next-month' );
							if ( previousStage !== stage ) {
								$td.addClass( 'first' );
							}
						}

						curMonth = month + stage;
						curYear = year;
						if ( curMonth < 0 ) {
							curMonth = 11;
							curYear--;
						} else if ( curMonth > 11 ) {
							curMonth = 0;
							curYear++;
						}

						$td.attr( {
							'data-date': curDate,
							'data-month': curMonth,
							'data-year': curYear
						} );
						if ( curYear === nowYear && curMonth === nowMonth && curDate === nowDate ) {
							$td.addClass( 'current-day' );
						} else if ( curYear < nowYear || ( curYear === nowYear && curMonth < nowMonth ) ||
							( curYear === nowYear && curMonth === nowMonth && curDate < nowDate ) ) {
							$td.addClass( 'past' );
							if ( !this.options.allowPastDates ) {
								$td.addClass( 'restricted' ).attr( 'title', this.restrictedText );
							}

						}

						if ( this.isRestricted( curDate, curMonth, curYear ) ) {
							$td.addClass( 'restricted' ).attr( 'title', this.restrictedText );
						}

						if ( selected && curYear === selected.year && curMonth === selected.month && curDate === selected.date ) {
							$td.addClass( 'selected' );
						}

						if ( $td.hasClass( 'restricted' ) ) {
							$td.html( '<span><b class="datepicker-date">' + curDate + '</b></span>' );
						} else {
							$td.html( '<span><button type="button" class="datepicker-date">' + curDate + '</button></span>' );
						}

						curDate++;
						lastStage = previousStage;
						previousStage = stage;
						if ( stage === -1 && curDate > lastMonthDate ) {
							curDate = 1;
							stage = 0;
							if ( lastStage !== stage ) {
								$td.addClass( 'last' );
							}
						} else if ( stage === 0 && curDate > lastDate ) {
							curDate = 1;
							stage = 1;
							if ( lastStage !== stage ) {
								$td.addClass( 'last' );
							}
						}
						if ( i === ( rows - 1 ) && j === 6 ) {
							$td.addClass( 'last' );
						}

						$tr.append( $td );
					}
					$tbody.append( $tr );
				}
			},

			renderWheel: function( date ) {
				var month = date.getMonth();
				var $monthUl = this.$wheelsMonth.find( 'ul' );
				var year = date.getFullYear();
				var $yearUl = this.$wheelsYear.find( 'ul' );
				var i, $monthSelected, $yearSelected;

				if ( this.sameYearOnly ) {
					this.$wheelsMonth.addClass( 'full' );
					this.$wheelsYear.addClass( 'hidden' );
				} else {
					this.$wheelsMonth.removeClass( 'full' );
					this.$wheelsYear.removeClass( 'hide hidden' ); // .hide is deprecated
				}

				$monthUl.find( '.selected' ).removeClass( 'selected' );
				$monthSelected = $monthUl.find( 'li[data-month="' + month + '"]' );
				$monthSelected.addClass( 'selected' );
				$monthUl.scrollTop( $monthUl.scrollTop() + ( $monthSelected.position().top - $monthUl.outerHeight() / 2 - $monthSelected.outerHeight( true ) / 2 ) );

				$yearUl.empty();
				for ( i = ( year - 10 ); i < ( year + 11 ); i++ ) {
					$yearUl.append( '<li data-year="' + i + '"><button type="button">' + i + '</button></li>' );
				}
				$yearSelected = $yearUl.find( 'li[data-year="' + year + '"]' );
				$yearSelected.addClass( 'selected' );
				this.artificialScrolling = true;
				$yearUl.scrollTop( $yearUl.scrollTop() + ( $yearSelected.position().top - $yearUl.outerHeight() / 2 - $yearSelected.outerHeight( true ) / 2 ) );
				this.artificialScrolling = false;
				$monthSelected.find( 'button' ).focus();
			},

			selectClicked: function() {
				var month = this.$wheelsMonth.find( '.selected' ).attr( 'data-month' );
				var year = this.$wheelsYear.find( '.selected' ).attr( 'data-year' );
				this.changeView( 'calendar', this.dateCalendar( year, month, 1 ) );
			},

			setCulture: function( cultureCode ) {
				if ( !cultureCode ) {
					return false;
				}

				if ( this.moment ) {
					moment.locale( cultureCode );
				} else {
					throw MOMENT_NOT_AVAILABLE;
				}
			},

			setDate: function( date ) {
				var parsed = this.parseDate( date );
				if ( !this.isInvalidDate( parsed ) ) {
					if ( !this.isRestricted( parsed.getDate(), parsed.getMonth(), parsed.getFullYear() ) ) {
						this.selectedDate = parsed;
						this.renderMonth( parsed );
						this.$input.val( this.formatDate( parsed ) );
					} else {
						this.selectedDate = false;
						this.renderMonth();
					}

				} else {
					this.selectedDate = null;
					this.renderMonth();
				}

				this.inputValue = this.$input.val();
				return this.selectedDate;
			},

			setFormat: function( format ) {
				if ( !format ) {
					return false;
				}

				if ( this.moment ) {
					this.momentFormat = format;
				} else {
					throw MOMENT_NOT_AVAILABLE;
				}
			},

			setRestrictedDates: function( restricted ) {
				var parsed = [];
				var self = this;
				var i, l;

				var parseItem = function( val ) {
					if ( val === -Infinity ) {
						return {
							date: -Infinity,
							month: -Infinity,
							year: -Infinity
						};
					} else if ( val === Infinity ) {
						return {
							date: Infinity,
							month: Infinity,
							year: Infinity
						};
					} else {
						val = self.parseDate( val );
						return {
							date: val.getDate(),
							month: val.getMonth(),
							year: val.getFullYear()
						};
					}
				};

				this.restricted = restricted;
				for ( i = 0, l = restricted.length; i < l; i++ ) {
					parsed.push( {
						from: parseItem( restricted[ i ].from ),
						to: parseItem( restricted[ i ].to )
					} );
				}
				this.restrictedParsed = parsed;
			},

			titleClicked: function( e ) {
				this.changeView( 'wheels', this.dateCalendar( this.$headerTitle.attr( 'data-year' ), this.$headerTitle.attr( 'data-month' ), 1 ) );
			},

			todayClicked: function( e ) {
				var date = this.dateCalendar();

				if ( ( date.getMonth() + '' ) !== this.$headerTitle.attr( 'data-month' ) || ( date.getFullYear() + '' ) !== this.$headerTitle.attr( 'data-year' ) ) {
					this.renderMonth( date );
				}
			},

			yearClicked: function( e ) {
				this.$wheelsYear.find( '.selected' ).removeClass( 'selected' );
				$( e.currentTarget ).parent().addClass( 'selected' );
			}
		};
		//for control library consistency
		Datepicker.prototype.getValue = Datepicker.prototype.getDate;

		// DATEPICKER PLUGIN DEFINITION

		$.fn.datepicker = function( option ) {
			var args = Array.prototype.slice.call( arguments, 1 );
			var methodReturn;

			var $set = this.each( function() {
				var $this = $( this );
				var data = $this.data( 'fu.datepicker' );
				var options = typeof option === 'object' && option;

				if ( !data ) {
					$this.data( 'fu.datepicker', ( data = new Datepicker( this, options ) ) );
				}

				if ( typeof option === 'string' ) {
					methodReturn = data[ option ].apply( data, args );
				}
			} );

			return ( methodReturn === undefined ) ? $set : methodReturn;
		};

		$.fn.datepicker.defaults = {
			allowPastDates: true,
			date: null,
			calendar: 'gregorian',
			formatDate: null,
			momentConfig: {
				culture: 'en',
				format: 'L' // more formats can be found here http://momentjs.com/docs/#/customization/long-date-formats/.
			},
			parseDate: null,
			restricted: [], //accepts an array of objects formatted as so: { from: {{date}}, to: {{date}} }  (ex: [ { from: new Date('12/11/2014'), to: new Date('03/31/2015') } ])
			restrictedText: 'Restricted',
			sameYearOnly: false
		};

		$.fn.datepicker.Constructor = Datepicker;

		$.fn.datepicker.noConflict = function() {
			$.fn.datepicker = old;
			return this;
		};

		// DATA-API

		$( document ).on( 'mousedown.fu.datepicker.data-api', '[data-initialize=datepicker]', function( e ) {
			var $control = $( e.target ).closest( '.datepicker' );
			if ( !$control.data( 'datepicker' ) ) {
				$control.datepicker( $control.data() );
			}
		} );

		//used to prevent the dropdown from closing when clicking within it's bounds
		$( document ).on( 'click.fu.datepicker.data-api', '.datepicker .dropdown-menu', function( e ) {
			var $target = $( e.target );
			if ( !$target.is( '.datepicker-date' ) || $target.closest( '.restricted' ).length ) {
				e.stopPropagation();
			}
		} );

		//used to prevent the dropdown from closing when clicking on the input
		$( document ).on( 'click.fu.datepicker.data-api', '.datepicker input', function( e ) {
			e.stopPropagation();
		} );

		$( function() {
			$( '[data-initialize=datepicker]' ).each( function() {
				var $this = $( this );
				if ( $this.data( 'datepicker' ) ) {
					return;
				}

				$this.datepicker( $this.data() );
			} );
		} );



	} )( jQuery );


	( function( $ ) {

		/* global jQuery:true */

		/*
		 * Fuel UX Dropdown Auto Flip
		 * https://github.com/ExactTarget/fuelux
		 *
		 * Copyright (c) 2014 ExactTarget
		 * Licensed under the BSD New license.
		 */



		// -- BEGIN MODULE CODE HERE --

		$( document ).on( 'click.fu.dropdown-autoflip', '[data-toggle=dropdown][data-flip]', function( event ) {
			if ( $( this ).data().flip === "auto" ) {
				// have the drop down decide where to place itself
				_autoFlip( $( this ).next( '.dropdown-menu' ) );
			}
		} );

		// For pillbox suggestions dropdown
		$( document ).on( 'suggested.fu.pillbox', function( event, element ) {
			_autoFlip( $( element ) );
			$( element ).parent().addClass( 'open' );
		} );

		function _autoFlip( menu ) {
			// hide while the browser thinks
			$( menu ).css( {
				visibility: "hidden"
			} );

			// decide where to put menu
			if ( dropUpCheck( menu ) ) {
				menu.parent().addClass( "dropup" );
			} else {
				menu.parent().removeClass( "dropup" );
			}

			// show again
			$( menu ).css( {
				visibility: "visible"
			} );
		}

		function dropUpCheck( element ) {
			// caching container
			var $container = _getContainer( element );

			// building object with measurementsances for later use
			var measurements = {};
			measurements.parentHeight = element.parent().outerHeight();
			measurements.parentOffsetTop = element.parent().offset().top;
			measurements.dropdownHeight = element.outerHeight();
			measurements.containerHeight = $container.overflowElement.outerHeight();

			// this needs to be different if the window is the container or another element is
			measurements.containerOffsetTop = ( !!$container.isWindow ) ? $container.overflowElement.scrollTop() : $container.overflowElement.offset().top;

			// doing the calculations
			measurements.fromTop = measurements.parentOffsetTop - measurements.containerOffsetTop;
			measurements.fromBottom = measurements.containerHeight - measurements.parentHeight - ( measurements.parentOffsetTop - measurements.containerOffsetTop );

			// actual determination of where to put menu
			// false = drop down
			// true = drop up
			if ( measurements.dropdownHeight < measurements.fromBottom ) {
				return false;
			} else if ( measurements.dropdownHeight < measurements.fromTop ) {
				return true;
			} else if ( measurements.dropdownHeight >= measurements.fromTop && measurements.dropdownHeight >= measurements.fromBottom ) {
				// decide which one is bigger and put it there
				if ( measurements.fromTop >= measurements.fromBottom ) {
					return true;
				} else {
					return false;
				}

			}

		}

		function _getContainer( element ) {
			var targetSelector = element.attr( 'data-target' );
			var isWindow = true;
			var containerElement;

			if ( !targetSelector ) {
				// no selection so find the relevant ancestor
				$.each( element.parents(), function( index, parentElement ) {
					if ( $( parentElement ).css( 'overflow' ) !== 'visible' ) {
						containerElement = parentElement;
						isWindow = false;
						return false;
					}
				} );
			} else if ( targetSelector !== 'window' ) {
				containerElement = $( targetSelector );
				isWindow = false;
			}

			// fallback to window
			if ( isWindow ) {
				containerElement = window;
			}

			return {
				overflowElement: $( containerElement ),
				isWindow: isWindow
			};
		}

		// register empty plugin
		$.fn.dropdownautoflip = function() {
			/* empty */
		};



	} )( jQuery );


	( function( $ ) {

		/* global jQuery:true */

		/*
		 * Fuel UX Loader
		 * https://github.com/ExactTarget/fuelux
		 *
		 * Copyright (c) 2014 ExactTarget
		 * Licensed under the BSD New license.
		 */



		// -- BEGIN MODULE CODE HERE --

		var old = $.fn.loader;

		// LOADER CONSTRUCTOR AND PROTOTYPE

		var Loader = function( element, options ) {
			this.$element = $( element );
			this.options = $.extend( {}, $.fn.loader.defaults, options );
		};

		Loader.prototype = {

			constructor: Loader,

			destroy: function() {
				this.$element.remove();
				// any external bindings
				// [none]
				// empty elements to return to original markup
				// [none]
				// returns string of markup
				return this.$element[ 0 ].outerHTML;
			},

			ieRepaint: function() {},

			msieVersion: function() {},

			next: function() {},

			pause: function() {},

			play: function() {},

			previous: function() {},

			reset: function() {}
		};

		// LOADER PLUGIN DEFINITION

		$.fn.loader = function( option ) {
			var args = Array.prototype.slice.call( arguments, 1 );
			var methodReturn;

			var $set = this.each( function() {
				var $this = $( this );
				var data = $this.data( 'fu.loader' );
				var options = typeof option === 'object' && option;

				if ( !data ) {
					$this.data( 'fu.loader', ( data = new Loader( this, options ) ) );
				}

				if ( typeof option === 'string' ) {
					methodReturn = data[ option ].apply( data, args );
				}
			} );

			return ( methodReturn === undefined ) ? $set : methodReturn;
		};

		$.fn.loader.defaults = {};

		$.fn.loader.Constructor = Loader;

		$.fn.loader.noConflict = function() {
			$.fn.loader = old;
			return this;
		};

		// INIT LOADER ON DOMCONTENTLOADED

		$( function() {
			$( '[data-initialize=loader]' ).each( function() {
				var $this = $( this );
				if ( !$this.data( 'fu.loader' ) ) {
					$this.loader( $this.data() );
				}
			} );
		} );



	} )( jQuery );


	( function( $ ) {

		/* global jQuery:true */

		/*
		 * Fuel UX Placard
		 * https://github.com/ExactTarget/fuelux
		 *
		 * Copyright (c) 2014 ExactTarget
		 * Licensed under the BSD New license.
		 */



		// -- BEGIN MODULE CODE HERE --

		var old = $.fn.placard;
		var EVENT_CALLBACK_MAP = {
			'accepted': 'onAccept',
			'cancelled': 'onCancel'
		};

		// PLACARD CONSTRUCTOR AND PROTOTYPE

		var Placard = function Placard( element, options ) {
			var self = this;
			this.$element = $( element );
			this.options = $.extend( {}, $.fn.placard.defaults, options );

			if ( this.$element.attr( 'data-ellipsis' ) === 'true' ) {
				this.options.applyEllipsis = true;
			}

			this.$accept = this.$element.find( '.placard-accept' );
			this.$cancel = this.$element.find( '.placard-cancel' );
			this.$field = this.$element.find( '.placard-field' );
			this.$footer = this.$element.find( '.placard-footer' );
			this.$header = this.$element.find( '.placard-header' );
			this.$popup = this.$element.find( '.placard-popup' );

			this.actualValue = null;
			this.clickStamp = '_';
			this.previousValue = '';
			if ( this.options.revertOnCancel === -1 ) {
				this.options.revertOnCancel = ( this.$accept.length > 0 );
			}

			// Placard supports inputs, textareas, or contenteditable divs. These checks determine which is being used
			this.isContentEditableDiv = this.$field.is( 'div' );
			this.isInput = this.$field.is( 'input' );
			this.divInTextareaMode = ( this.isContentEditableDiv && this.$field.attr( 'data-textarea' ) === 'true' );

			this.$field.on( 'focus.fu.placard', $.proxy( this.show, this ) );
			this.$field.on( 'keydown.fu.placard', $.proxy( this.keyComplete, this ) );
			this.$element.on( 'close.fu.placard', $.proxy( this.hide, this ) );
			this.$accept.on( 'click.fu.placard', $.proxy( this.complete, this, 'accepted' ) );
			this.$cancel.on( 'click.fu.placard', function( e ) {
				e.preventDefault();
				self.complete( 'cancelled' );
			} );

			this.applyEllipsis();
		};

		var _isShown = function _isShown( placard ) {
			return placard.$element.hasClass( 'showing' );
		};

		var _closeOtherPlacards = function _closeOtherPlacards() {
			var otherPlacards;

			otherPlacards = $( document ).find( '.placard.showing' );
			if ( otherPlacards.length > 0 ) {
				if ( otherPlacards.data( 'fu.placard' ) && otherPlacards.data( 'fu.placard' ).options.explicit ) {
					return false; //failed
				}

				otherPlacards.placard( 'externalClickListener', {}, true );
			}

			return true; //succeeded
		};

		Placard.prototype = {
			constructor: Placard,

			complete: function complete( action ) {
				var func = this.options[ EVENT_CALLBACK_MAP[ action ] ];

				var obj = {
					previousValue: this.previousValue,
					value: this.getValue()
				};

				if ( func ) {
					func( obj );
					this.$element.trigger( action + '.fu.placard', obj );
				} else {
					if ( action === 'cancelled' && this.options.revertOnCancel ) {
						this.setValue( this.previousValue, true );
					}

					this.$element.trigger( action + '.fu.placard', obj );
					this.hide();
				}
			},

			keyComplete: function keyComplete( e ) {
				if ( ( ( this.isContentEditableDiv && !this.divInTextareaMode ) || this.isInput ) && e.keyCode === 13 ) {
					this.complete( 'accepted' );
					this.$field.blur();
				} else if ( e.keyCode === 27 ) {
					this.complete( 'cancelled' );
					this.$field.blur();
				}
			},

			destroy: function destroy() {
				this.$element.remove();
				// remove any external bindings
				$( document ).off( 'click.fu.placard.externalClick.' + this.clickStamp );
				// set input value attribute
				this.$element.find( 'input' ).each( function() {
					$( this ).attr( 'value', $( this ).val() );
				} );
				// empty elements to return to original markup
				// [none]
				// return string of markup
				return this.$element[ 0 ].outerHTML;
			},

			disable: function disable() {
				this.$element.addClass( 'disabled' );
				this.$field.attr( 'disabled', 'disabled' );
				if ( this.isContentEditableDiv ) {
					this.$field.removeAttr( 'contenteditable' );
				}
				this.hide();
			},

			applyEllipsis: function applyEllipsis() {
				var field, i, str;
				if ( this.options.applyEllipsis ) {
					field = this.$field.get( 0 );
					if ( ( this.isContentEditableDiv && !this.divInTextareaMode ) || this.isInput ) {
						field.scrollLeft = 0;
					} else {
						field.scrollTop = 0;
						if ( field.clientHeight < field.scrollHeight ) {
							this.actualValue = this.getValue();
							this.setValue( '', true );
							str = '';
							i = 0;
							while ( field.clientHeight >= field.scrollHeight ) {
								str += this.actualValue[ i ];
								this.setValue( str + '...', true );
								i++;
							}
							str = ( str.length > 0 ) ? str.substring( 0, str.length - 1 ) : '';
							this.setValue( str + '...', true );
						}
					}

				}
			},

			enable: function enable() {
				this.$element.removeClass( 'disabled' );
				this.$field.removeAttr( 'disabled' );
				if ( this.isContentEditableDiv ) {
					this.$field.attr( 'contenteditable', 'true' );
				}
			},

			externalClickListener: function externalClickListener( e, force ) {
				if ( force === true || this.isExternalClick( e ) ) {
					this.complete( this.options.externalClickAction );
				}
			},

			getValue: function getValue() {
				if ( this.actualValue !== null ) {
					return this.actualValue;
				} else if ( this.isContentEditableDiv ) {
					return this.$field.html();
				} else {
					return this.$field.val();
				}
			},

			hide: function hide() {
				if ( !this.$element.hasClass( 'showing' ) ) {
					return;
				}

				this.$element.removeClass( 'showing' );
				this.applyEllipsis();
				$( document ).off( 'click.fu.placard.externalClick.' + this.clickStamp );
				this.$element.trigger( 'hidden.fu.placard' );
			},

			isExternalClick: function isExternalClick( e ) {
				var el = this.$element.get( 0 );
				var exceptions = this.options.externalClickExceptions || [];
				var $originEl = $( e.target );
				var i, l;

				if ( e.target === el || $originEl.parents( '.placard:first' ).get( 0 ) === el ) {
					return false;
				} else {
					for ( i = 0, l = exceptions.length; i < l; i++ ) {
						if ( $originEl.is( exceptions[ i ] ) || $originEl.parents( exceptions[ i ] ).length > 0 ) {
							return false;
						}

					}
				}

				return true;
			},

			/**
			 * setValue() sets the Placard triggering DOM element's display value
			 *
			 * @param {String} the value to be displayed
			 * @param {Boolean} If you want to explicitly suppress the application
			 *					of ellipsis, pass `true`. This would typically only be
			 *					done from internal functions (like `applyEllipsis`)
			 *					that want to avoid circular logic. Otherwise, the
			 *					value of the option applyEllipsis will be used.
			 * @return {Object} jQuery object representing the DOM element whose
			 *					value was set
			 */
			setValue: function setValue( val, suppressEllipsis ) {
				//if suppressEllipsis is undefined, check placards init settings
				if ( typeof suppressEllipsis === 'undefined' ) {
					suppressEllipsis = !this.options.applyEllipsis;
				}

				if ( this.isContentEditableDiv ) {
					this.$field.empty().append( val );
				} else {
					this.$field.val( val );
				}

				if ( !suppressEllipsis && !_isShown( this ) ) {
					this.applyEllipsis();
				}

				return this.$field;
			},

			show: function show() {
				if ( _isShown( this ) ) {
					return;
				}
				if ( !_closeOtherPlacards() ) {
					return;
				}

				this.previousValue = ( this.isContentEditableDiv ) ? this.$field.html() : this.$field.val();

				if ( this.actualValue !== null ) {
					this.setValue( this.actualValue, true );
					this.actualValue = null;
				}

				this.showPlacard();
			},

			showPlacard: function showPlacard() {
				this.$element.addClass( 'showing' );

				if ( this.$header.length > 0 ) {
					this.$popup.css( 'top', '-' + this.$header.outerHeight( true ) + 'px' );
				}

				if ( this.$footer.length > 0 ) {
					this.$popup.css( 'bottom', '-' + this.$footer.outerHeight( true ) + 'px' );
				}

				this.$element.trigger( 'shown.fu.placard' );
				this.clickStamp = new Date().getTime() + ( Math.floor( Math.random() * 100 ) + 1 );
				if ( !this.options.explicit ) {
					$( document ).on( 'click.fu.placard.externalClick.' + this.clickStamp, $.proxy( this.externalClickListener, this ) );
				}
			}
		};

		// PLACARD PLUGIN DEFINITION

		$.fn.placard = function( option ) {
			var args = Array.prototype.slice.call( arguments, 1 );
			var methodReturn;

			var $set = this.each( function() {
				var $this = $( this );
				var data = $this.data( 'fu.placard' );
				var options = typeof option === 'object' && option;

				if ( !data ) {
					$this.data( 'fu.placard', ( data = new Placard( this, options ) ) );
				}

				if ( typeof option === 'string' ) {
					methodReturn = data[ option ].apply( data, args );
				}
			} );

			return ( methodReturn === undefined ) ? $set : methodReturn;
		};

		$.fn.placard.defaults = {
			onAccept: undefined,
			onCancel: undefined,
			externalClickAction: 'cancelled',
			externalClickExceptions: [],
			explicit: false,
			revertOnCancel: -1, //negative 1 will check for an '.placard-accept' button. Also can be set to true or false
			applyEllipsis: false
		};

		$.fn.placard.Constructor = Placard;

		$.fn.placard.noConflict = function() {
			$.fn.placard = old;
			return this;
		};

		// DATA-API

		$( document ).on( 'focus.fu.placard.data-api', '[data-initialize=placard]', function( e ) {
			var $control = $( e.target ).closest( '.placard' );
			if ( !$control.data( 'fu.placard' ) ) {
				$control.placard( $control.data() );
			}
		} );

		// Must be domReady for AMD compatibility
		$( function() {
			$( '[data-initialize=placard]' ).each( function() {
				var $this = $( this );
				if ( $this.data( 'fu.placard' ) ) return;
				$this.placard( $this.data() );
			} );
		} );



	} )( jQuery );


	( function( $ ) {

		/* global jQuery:true */

		/*
		 * Fuel UX Radio
		 * https://github.com/ExactTarget/fuelux
		 *
		 * Copyright (c) 2014 ExactTarget
		 * Licensed under the BSD New license.
		 */



		// -- BEGIN MODULE CODE HERE --

		var old = $.fn.radio;

		// RADIO CONSTRUCTOR AND PROTOTYPE
		var logError = function logError( error ) {
			if ( window && window.console && window.console.error ) {
				window.console.error( error );
			}
		};

		var Radio = function Radio( element, options ) {
			this.options = $.extend( {}, $.fn.radio.defaults, options );

			if ( element.tagName.toLowerCase() !== 'label' ) {
				logError( 'Radio must be initialized on the `label` that wraps the `input` element. See https://github.com/ExactTarget/fuelux/blob/master/reference/markup/radio.html for example of proper markup. Call `.radio()` on the `<label>` not the `<input>`' );
				return;
			}

			// cache elements
			this.$label = $( element );
			this.$radio = this.$label.find( 'input[type="radio"]' );
			this.groupName = this.$radio.attr( 'name' ); // don't cache group itself since items can be added programmatically

			if ( !this.options.ignoreVisibilityCheck && this.$radio.css( 'visibility' ).match( /hidden|collapse/ ) ) {
				logError( 'For accessibility reasons, in order for tab and space to function on radio, `visibility` must not be set to `hidden` or `collapse`. See https://github.com/ExactTarget/fuelux/pull/1996 for more details.' );
			}

			// determine if a toggle container is specified
			var containerSelector = this.$radio.attr( 'data-toggle' );
			this.$toggleContainer = $( containerSelector );

			// handle internal events
			this.$radio.on( 'change', $.proxy( this.itemchecked, this ) );

			// set default state
			this.setInitialState();
		};

		Radio.prototype = {

			constructor: Radio,

			setInitialState: function setInitialState() {
				var $radio = this.$radio;

				// get current state of input
				var checked = $radio.prop( 'checked' );
				var disabled = $radio.prop( 'disabled' );

				// sync label class with input state
				this.setCheckedState( $radio, checked );
				this.setDisabledState( $radio, disabled );
			},

			resetGroup: function resetGroup() {
				var $radios = $( 'input[name="' + this.groupName + '"]' );
				$radios.each( function resetRadio( index, item ) {
					var $radio = $( item );
					var $lbl = $radio.parent();
					var containerSelector = $radio.attr( 'data-toggle' );
					var $containerToggle = $( containerSelector );


					$lbl.removeClass( 'checked' );
					$containerToggle.addClass( 'hidden' );
				} );
			},

			setCheckedState: function setCheckedState( element, checked ) {
				var $radio = element;
				var $lbl = $radio.parent();
				var containerSelector = $radio.attr( 'data-toggle' );
				var $containerToggle = $( containerSelector );

				if ( checked ) {
					// reset all items in group
					this.resetGroup();

					$radio.prop( 'checked', true );
					$lbl.addClass( 'checked' );
					$containerToggle.removeClass( 'hide hidden' );
					$lbl.trigger( 'checked.fu.radio' );
				} else {
					$radio.prop( 'checked', false );
					$lbl.removeClass( 'checked' );
					$containerToggle.addClass( 'hidden' );
					$lbl.trigger( 'unchecked.fu.radio' );
				}

				$lbl.trigger( 'changed.fu.radio', checked );
			},

			setDisabledState: function setDisabledState( element, disabled ) {
				var $radio = $( element );
				var $lbl = this.$label;

				if ( disabled ) {
					$radio.prop( 'disabled', true );
					$lbl.addClass( 'disabled' );
					$lbl.trigger( 'disabled.fu.radio' );
				} else {
					$radio.prop( 'disabled', false );
					$lbl.removeClass( 'disabled' );
					$lbl.trigger( 'enabled.fu.radio' );
				}

				return $radio;
			},

			itemchecked: function itemchecked( evt ) {
				var $radio = $( evt.target );
				this.setCheckedState( $radio, true );
			},

			check: function check() {
				this.setCheckedState( this.$radio, true );
			},

			uncheck: function uncheck() {
				this.setCheckedState( this.$radio, false );
			},

			isChecked: function isChecked() {
				var checked = this.$radio.prop( 'checked' );
				return checked;
			},

			enable: function enable() {
				this.setDisabledState( this.$radio, false );
			},

			disable: function disable() {
				this.setDisabledState( this.$radio, true );
			},

			destroy: function destroy() {
				this.$label.remove();
				return this.$label[ 0 ].outerHTML;
			}
		};

		Radio.prototype.getValue = Radio.prototype.isChecked;

		// RADIO PLUGIN DEFINITION

		$.fn.radio = function radio( option ) {
			var args = Array.prototype.slice.call( arguments, 1 );
			var methodReturn;

			var $set = this.each( function applyData() {
				var $this = $( this );
				var data = $this.data( 'fu.radio' );
				var options = typeof option === 'object' && option;

				if ( !data ) {
					$this.data( 'fu.radio', ( data = new Radio( this, options ) ) );
				}

				if ( typeof option === 'string' ) {
					methodReturn = data[ option ].apply( data, args );
				}
			} );

			return ( methodReturn === undefined ) ? $set : methodReturn;
		};

		$.fn.radio.defaults = {
			ignoreVisibilityCheck: false
		};

		$.fn.radio.Constructor = Radio;

		$.fn.radio.noConflict = function noConflict() {
			$.fn.radio = old;
			return this;
		};


		// DATA-API

		$( document ).on( 'mouseover.fu.radio.data-api', '[data-initialize=radio]', function initializeRadios( e ) {
			var $control = $( e.target );
			if ( !$control.data( 'fu.radio' ) ) {
				$control.radio( $control.data() );
			}
		} );

		// Must be domReady for AMD compatibility
		$( function onReadyInitializeRadios() {
			$( '[data-initialize=radio]' ).each( function initializeRadio() {
				var $this = $( this );
				if ( !$this.data( 'fu.radio' ) ) {
					$this.radio( $this.data() );
				}
			} );
		} );



	} )( jQuery );


	( function( $ ) {

		/* global jQuery:true */

		/*
		 * Fuel UX Search
		 * https://github.com/ExactTarget/fuelux
		 *
		 * Copyright (c) 2014 ExactTarget
		 * Licensed under the BSD New license.
		 */



		// -- BEGIN MODULE CODE HERE --

		var old = $.fn.search;

		// SEARCH CONSTRUCTOR AND PROTOTYPE

		var Search = function( element, options ) {
			this.$element = $( element );
			this.$repeater = $( element ).closest( '.repeater' );
			this.options = $.extend( {}, $.fn.search.defaults, options );

			if ( this.$element.attr( 'data-searchOnKeyPress' ) === 'true' ) {
				this.options.searchOnKeyPress = true;
			}

			this.$button = this.$element.find( 'button' );
			this.$input = this.$element.find( 'input' );
			this.$icon = this.$element.find( '.glyphicon, .fuelux-icon' );

			this.$button.on( 'click.fu.search', $.proxy( this.buttonclicked, this ) );
			this.$input.on( 'keyup.fu.search', $.proxy( this.keypress, this ) );

			if ( this.$repeater.length > 0 ) {
				this.$repeater.on( 'rendered.fu.repeater', $.proxy( this.clearPending, this ) );
			}

			this.activeSearch = '';
		};

		Search.prototype = {
			constructor: Search,

			destroy: function() {
				this.$element.remove();
				// any external bindings
				// [none]
				// set input value attrbute
				this.$element.find( 'input' ).each( function() {
					$( this ).attr( 'value', $( this ).val() );
				} );
				// empty elements to return to original markup
				// [none]
				// returns string of markup
				return this.$element[ 0 ].outerHTML;
			},

			search: function( searchText ) {
				if ( this.$icon.hasClass( 'glyphicon' ) ) {
					this.$icon.removeClass( 'glyphicon-search' ).addClass( 'glyphicon-remove' );
				}
				if ( this.$icon.hasClass( 'fuelux-icon' ) ) {
					this.$icon.removeClass( 'fuelux-icon-search' ).addClass( 'fuelux-icon-remove' );
				}

				this.activeSearch = searchText;
				this.$element.addClass( 'searched pending' );
				this.$element.trigger( 'searched.fu.search', searchText );
			},

			clear: function() {
				if ( this.$icon.hasClass( 'glyphicon' ) ) {
					this.$icon.removeClass( 'glyphicon-remove' ).addClass( 'glyphicon-search' );
				}
				if ( this.$icon.hasClass( 'fuelux-icon' ) ) {
					this.$icon.removeClass( 'fuelux-icon-remove' ).addClass( 'fuelux-icon-search' );
				}

				if ( this.$element.hasClass( 'pending' ) ) {
					this.$element.trigger( 'canceled.fu.search' );
				}

				this.activeSearch = '';
				this.$input.val( '' );
				this.$element.trigger( 'cleared.fu.search' );
				this.$element.removeClass( 'searched pending' );
			},

			clearPending: function() {
				this.$element.removeClass( 'pending' );
			},

			action: function() {
				var val = this.$input.val();

				if ( val && val.length > 0 ) {
					this.search( val );
				} else {
					this.clear();
				}
			},

			buttonclicked: function( e ) {
				e.preventDefault();
				if ( $( e.currentTarget ).is( '.disabled, :disabled' ) ) return;

				if ( this.$element.hasClass( 'pending' ) || this.$element.hasClass( 'searched' ) ) {
					this.clear();
				} else {
					this.action();
				}
			},

			keypress: function( e ) {
				var ENTER_KEY_CODE = 13;
				var TAB_KEY_CODE = 9;
				var ESC_KEY_CODE = 27;

				if ( e.which === ENTER_KEY_CODE ) {
					e.preventDefault();
					this.action();
				} else if ( e.which === TAB_KEY_CODE ) {
					e.preventDefault();
				} else if ( e.which === ESC_KEY_CODE ) {
					e.preventDefault();
					this.clear();
				} else if ( this.options.searchOnKeyPress ) {
					// search on other keypress
					this.action();
				}
			},

			disable: function() {
				this.$element.addClass( 'disabled' );
				this.$input.attr( 'disabled', 'disabled' );

				if ( !this.options.allowCancel ) {
					this.$button.addClass( 'disabled' );
				}
			},

			enable: function() {
				this.$element.removeClass( 'disabled' );
				this.$input.removeAttr( 'disabled' );
				this.$button.removeClass( 'disabled' );
			}
		};


		// SEARCH PLUGIN DEFINITION

		$.fn.search = function( option ) {
			var args = Array.prototype.slice.call( arguments, 1 );
			var methodReturn;

			var $set = this.each( function() {
				var $this = $( this );
				var data = $this.data( 'fu.search' );
				var options = typeof option === 'object' && option;

				if ( !data ) {
					$this.data( 'fu.search', ( data = new Search( this, options ) ) );
				}

				if ( typeof option === 'string' ) {
					methodReturn = data[ option ].apply( data, args );
				}
			} );

			return ( methodReturn === undefined ) ? $set : methodReturn;
		};

		$.fn.search.defaults = {
			clearOnEmpty: false,
			searchOnKeyPress: false,
			allowCancel: false
		};

		$.fn.search.Constructor = Search;

		$.fn.search.noConflict = function() {
			$.fn.search = old;
			return this;
		};


		// DATA-API

		$( document ).on( 'mousedown.fu.search.data-api', '[data-initialize=search]', function( e ) {
			var $control = $( e.target ).closest( '.search' );
			if ( !$control.data( 'fu.search' ) ) {
				$control.search( $control.data() );
			}
		} );

		// Must be domReady for AMD compatibility
		$( function() {
			$( '[data-initialize=search]' ).each( function() {
				var $this = $( this );
				if ( $this.data( 'fu.search' ) ) return;
				$this.search( $this.data() );
			} );
		} );



	} )( jQuery );


	( function( $ ) {

		/* global jQuery:true */

		/*
		 * Fuel UX Selectlist
		 * https://github.com/ExactTarget/fuelux
		 *
		 * Copyright (c) 2014 ExactTarget
		 * Licensed under the BSD New license.
		 */



		// -- BEGIN MODULE CODE HERE --

		var old = $.fn.selectlist;
		// SELECT CONSTRUCTOR AND PROTOTYPE

		var Selectlist = function( element, options ) {
			this.$element = $( element );
			this.options = $.extend( {}, $.fn.selectlist.defaults, options );


			this.$button = this.$element.find( '.btn.dropdown-toggle' );
			this.$hiddenField = this.$element.find( '.hidden-field' );
			this.$label = this.$element.find( '.selected-label' );
			this.$dropdownMenu = this.$element.find( '.dropdown-menu' );

			this.$element.on( 'click.fu.selectlist', '.dropdown-menu a', $.proxy( this.itemClicked, this ) );
			this.setDefaultSelection();

			if ( options.resize === 'auto' || this.$element.attr( 'data-resize' ) === 'auto' ) {
				this.resize();
			}

			// if selectlist is empty or is one item, disable it
			var items = this.$dropdownMenu.children( 'li' );
			if ( items.length === 0 ) {
				this.disable();
				this.doSelect( $( this.options.emptyLabelHTML ) );
			}

			// support jumping focus to first letter in dropdown when key is pressed
			this.$element.on( 'shown.bs.dropdown', function() {
				var $this = $( this );
				// attach key listener when dropdown is shown
				$( document ).on( 'keypress.fu.selectlist', function( e ) {

					// get the key that was pressed
					var key = String.fromCharCode( e.which );
					// look the items to find the first item with the first character match and set focus
					$this.find( "li" ).each( function( idx, item ) {
						if ( $( item ).text().charAt( 0 ).toLowerCase() === key ) {
							$( item ).children( 'a' ).focus();
							return false;
						}
					} );

				} );
			} );

			// unbind key event when dropdown is hidden
			this.$element.on( 'hide.bs.dropdown', function() {
				$( document ).off( 'keypress.fu.selectlist' );
			} );
		};

		Selectlist.prototype = {

			constructor: Selectlist,

			destroy: function() {
				this.$element.remove();
				// any external bindings
				// [none]
				// empty elements to return to original markup
				// [none]
				// returns string of markup
				return this.$element[ 0 ].outerHTML;
			},

			doSelect: function( $item ) {
				var $selectedItem;
				this.$selectedItem = $selectedItem = $item;

				this.$hiddenField.val( this.$selectedItem.attr( 'data-value' ) );
				this.$label.html( $( this.$selectedItem.children()[ 0 ] ).html() );

				// clear and set selected item to allow declarative init state
				// unlike other controls, selectlist's value is stored internal, not in an input
				this.$element.find( 'li' ).each( function() {
					if ( $selectedItem.is( $( this ) ) ) {
						$( this ).attr( 'data-selected', true );
					} else {
						$( this ).removeData( 'selected' ).removeAttr( 'data-selected' );
					}
				} );
			},

			itemClicked: function( e ) {
				this.$element.trigger( 'clicked.fu.selectlist', this.$selectedItem );

				e.preventDefault();
				// ignore if a disabled item is clicked
				if ( $( e.currentTarget ).parent( 'li' ).is( '.disabled, :disabled' ) ) {
					return;
				}

				// is clicked element different from currently selected element?
				if ( !( $( e.target ).parent().is( this.$selectedItem ) ) ) {
					this.itemChanged( e );
				}

				// return focus to control after selecting an option
				this.$element.find( '.dropdown-toggle' ).focus();
			},

			itemChanged: function( e ) {
				//selectedItem needs to be <li> since the data is stored there, not in <a>
				this.doSelect( $( e.target ).closest( 'li' ) );

				// pass object including text and any data-attributes
				// to onchange event
				var data = this.selectedItem();
				// trigger changed event
				this.$element.trigger( 'changed.fu.selectlist', data );
			},

			resize: function() {
				var width = 0;
				var newWidth = 0;
				var sizer = $( '<div/>' ).addClass( 'selectlist-sizer' );


				if ( Boolean( $( document ).find( 'html' ).hasClass( 'fuelux' ) ) ) {
					// default behavior for fuel ux setup. means fuelux was a class on the html tag
					$( document.body ).append( sizer );
				} else {
					// fuelux is not a class on the html tag. So we'll look for the first one we find so the correct styles get applied to the sizer
					$( '.fuelux:first' ).append( sizer );
				}

				sizer.append( this.$element.clone() );

				this.$element.find( 'a' ).each( function() {
					sizer.find( '.selected-label' ).text( $( this ).text() );
					newWidth = sizer.find( '.selectlist' ).outerWidth();
					newWidth = newWidth + sizer.find( '.sr-only' ).outerWidth();
					if ( newWidth > width ) {
						width = newWidth;
					}
				} );

				if ( width <= 1 ) {
					return;
				}

				this.$button.css( 'width', width );
				this.$dropdownMenu.css( 'width', width );

				sizer.remove();
			},

			selectedItem: function() {
				var txt = this.$selectedItem.text();
				return $.extend( {
					text: txt
				}, this.$selectedItem.data() );
			},

			selectByText: function( text ) {
				var $item = $( [] );
				this.$element.find( 'li' ).each( function() {
					if ( ( this.textContent || this.innerText || $( this ).text() || '' ).toLowerCase() === ( text || '' ).toLowerCase() ) {
						$item = $( this );
						return false;
					}
				} );
				this.doSelect( $item );
			},

			selectByValue: function( value ) {
				var selector = 'li[data-value="' + value + '"]';
				this.selectBySelector( selector );
			},

			selectByIndex: function( index ) {
				// zero-based index
				var selector = 'li:eq(' + index + ')';
				this.selectBySelector( selector );
			},

			selectBySelector: function( selector ) {
				var $item = this.$element.find( selector );
				this.doSelect( $item );
			},

			setDefaultSelection: function() {
				var $item = this.$element.find( 'li[data-selected=true]' ).eq( 0 );

				if ( $item.length === 0 ) {
					$item = this.$element.find( 'li' ).has( 'a' ).eq( 0 );
				}

				this.doSelect( $item );
			},

			enable: function() {
				this.$element.removeClass( 'disabled' );
				this.$button.removeClass( 'disabled' );
			},

			disable: function() {
				this.$element.addClass( 'disabled' );
				this.$button.addClass( 'disabled' );
			}
		};

		Selectlist.prototype.getValue = Selectlist.prototype.selectedItem;


		// SELECT PLUGIN DEFINITION

		$.fn.selectlist = function( option ) {
			var args = Array.prototype.slice.call( arguments, 1 );
			var methodReturn;

			var $set = this.each( function() {
				var $this = $( this );
				var data = $this.data( 'fu.selectlist' );
				var options = typeof option === 'object' && option;

				if ( !data ) {
					$this.data( 'fu.selectlist', ( data = new Selectlist( this, options ) ) );
				}

				if ( typeof option === 'string' ) {
					methodReturn = data[ option ].apply( data, args );
				}
			} );

			return ( methodReturn === undefined ) ? $set : methodReturn;
		};

		$.fn.selectlist.defaults = {
			emptyLabelHTML: '<li data-value=""><a href="#">No items</a></li>'
		};

		$.fn.selectlist.Constructor = Selectlist;

		$.fn.selectlist.noConflict = function() {
			$.fn.selectlist = old;
			return this;
		};


		// DATA-API

		$( document ).on( 'mousedown.fu.selectlist.data-api', '[data-initialize=selectlist]', function( e ) {
			var $control = $( e.target ).closest( '.selectlist' );
			if ( !$control.data( 'fu.selectlist' ) ) {
				$control.selectlist( $control.data() );
			}
		} );

		// Must be domReady for AMD compatibility
		$( function() {
			$( '[data-initialize=selectlist]' ).each( function() {
				var $this = $( this );
				if ( !$this.data( 'fu.selectlist' ) ) {
					$this.selectlist( $this.data() );
				}
			} );
		} );



	} )( jQuery );


	( function( $ ) {

		/* global jQuery:true */

		/*
		 * Fuel UX Spinbox
		 * https://github.com/ExactTarget/fuelux
		 *
		 * Copyright (c) 2014 ExactTarget
		 * Licensed under the BSD New license.
		 */



		// -- BEGIN MODULE CODE HERE --

		var old = $.fn.spinbox;

		// SPINBOX CONSTRUCTOR AND PROTOTYPE

		var Spinbox = function Spinbox( element, options ) {
			this.$element = $( element );
			this.$element.find( '.btn' ).on( 'click', function( e ) {
				//keep spinbox from submitting if they forgot to say type="button" on their spinner buttons
				e.preventDefault();
			} );
			this.options = $.extend( {}, $.fn.spinbox.defaults, options );
			this.options.step = this.$element.data( 'step' ) || this.options.step;

			if ( this.options.value < this.options.min ) {
				this.options.value = this.options.min;
			} else if ( this.options.max < this.options.value ) {
				this.options.value = this.options.max;
			}

			this.$input = this.$element.find( '.spinbox-input' );
			this.$input.on( 'focusout.fu.spinbox', this.$input, $.proxy( this.change, this ) );
			this.$element.on( 'keydown.fu.spinbox', this.$input, $.proxy( this.keydown, this ) );
			this.$element.on( 'keyup.fu.spinbox', this.$input, $.proxy( this.keyup, this ) );

			if ( this.options.hold ) {
				this.$element.on( 'mousedown.fu.spinbox', '.spinbox-up', $.proxy( function() {
					this.startSpin( true );
				}, this ) );
				this.$element.on( 'mouseup.fu.spinbox', '.spinbox-up, .spinbox-down', $.proxy( this.stopSpin, this ) );
				this.$element.on( 'mouseout.fu.spinbox', '.spinbox-up, .spinbox-down', $.proxy( this.stopSpin, this ) );
				this.$element.on( 'mousedown.fu.spinbox', '.spinbox-down', $.proxy( function() {
					this.startSpin( false );
				}, this ) );
			} else {
				this.$element.on( 'click.fu.spinbox', '.spinbox-up', $.proxy( function() {
					this.step( true );
				}, this ) );
				this.$element.on( 'click.fu.spinbox', '.spinbox-down', $.proxy( function() {
					this.step( false );
				}, this ) );
			}

			this.switches = {
				count: 1,
				enabled: true
			};

			if ( this.options.speed === 'medium' ) {
				this.switches.speed = 300;
			} else if ( this.options.speed === 'fast' ) {
				this.switches.speed = 100;
			} else {
				this.switches.speed = 500;
			}

			this.options.defaultUnit = _isUnitLegal( this.options.defaultUnit, this.options.units ) ? this.options.defaultUnit : '';
			this.unit = this.options.defaultUnit;

			this.lastValue = this.options.value;

			this.render();

			if ( this.options.disabled ) {
				this.disable();
			}
		};

		// Truly private methods
		var _limitToStep = function _limitToStep( number, step ) {
			return Math.round( number / step ) * step;
		};

		var _isUnitLegal = function _isUnitLegal( unit, validUnits ) {
			var legalUnit = false;
			var suspectUnit = unit.toLowerCase();

			$.each( validUnits, function( i, validUnit ) {
				validUnit = validUnit.toLowerCase();
				if ( suspectUnit === validUnit ) {
					legalUnit = true;
					return false; //break out of the loop
				}
			} );

			return legalUnit;
		};

		var _applyLimits = function _applyLimits( value ) {
			// if unreadable
			if ( isNaN( parseFloat( value ) ) ) {
				return value;
			}

			// if not within range return the limit
			if ( value > this.options.max ) {
				if ( this.options.cycle ) {
					value = this.options.min;
				} else {
					value = this.options.max;
				}
			} else if ( value < this.options.min ) {
				if ( this.options.cycle ) {
					value = this.options.max;
				} else {
					value = this.options.min;
				}
			}

			if ( this.options.limitToStep && this.options.step ) {
				value = _limitToStep( value, this.options.step );

				//force round direction so that it stays within bounds
				if ( value > this.options.max ) {
					value = value - this.options.step;
				} else if ( value < this.options.min ) {
					value = value + this.options.step;
				}
			}

			return value;
		};

		Spinbox.prototype = {
			constructor: Spinbox,

			destroy: function destroy() {
				this.$element.remove();
				// any external bindings
				// [none]
				// set input value attrbute
				this.$element.find( 'input' ).each( function() {
					$( this ).attr( 'value', $( this ).val() );
				} );
				// empty elements to return to original markup
				// [none]
				// returns string of markup
				return this.$element[ 0 ].outerHTML;
			},

			render: function render() {
				this._setValue( this.getDisplayValue() );
			},

			change: function change() {
				this._setValue( this.getDisplayValue() );

				this.triggerChangedEvent();
			},

			stopSpin: function stopSpin() {
				if ( this.switches.timeout !== undefined ) {
					clearTimeout( this.switches.timeout );
					this.switches.count = 1;
					this.triggerChangedEvent();
				}
			},

			triggerChangedEvent: function triggerChangedEvent() {
				var currentValue = this.getValue();
				if ( currentValue === this.lastValue ) return;
				this.lastValue = currentValue;

				// Primary changed event
				this.$element.trigger( 'changed.fu.spinbox', currentValue );
			},

			startSpin: function startSpin( type ) {
				if ( !this.options.disabled ) {
					var divisor = this.switches.count;

					if ( divisor === 1 ) {
						this.step( type );
						divisor = 1;
					} else if ( divisor < 3 ) {
						divisor = 1.5;
					} else if ( divisor < 8 ) {
						divisor = 2.5;
					} else {
						divisor = 4;
					}

					this.switches.timeout = setTimeout( $.proxy( function() {
						this.iterate( type );
					}, this ), this.switches.speed / divisor );
					this.switches.count++;
				}
			},

			iterate: function iterate( type ) {
				this.step( type );
				this.startSpin( type );
			},

			step: function step( isIncrease ) {
				//refresh value from display before trying to increment in case they have just been typing before clicking the nubbins
				this._setValue( this.getDisplayValue() );
				var newVal;

				if ( isIncrease ) {
					newVal = this.options.value + this.options.step;
				} else {
					newVal = this.options.value - this.options.step;
				}

				newVal = newVal.toFixed( 5 );

				this._setValue( newVal + this.unit );
			},

			getDisplayValue: function getDisplayValue() {
				var inputValue = this.parseInput( this.$input.val() );
				var value = ( !!inputValue ) ? inputValue : this.options.value;
				return value;
			},

			setDisplayValue: function setDisplayValue( value ) {
				this.$input.val( value );
			},

			getValue: function getValue() {
				var val = this.options.value;
				if ( this.options.decimalMark !== '.' ) {
					val = ( val + '' ).split( '.' ).join( this.options.decimalMark );
				}
				return val + this.unit;
			},

			setValue: function setValue( val ) {
				return this._setValue( val, true );
			},

			_setValue: function _setValue( val, shouldSetLastValue ) {
				//remove any i18n on the number
				if ( this.options.decimalMark !== '.' ) {
					val = this.parseInput( val );
				}

				//are we dealing with united numbers?
				if ( typeof val !== "number" ) {
					var potentialUnit = val.replace( /[0-9.-]/g, '' );
					//make sure unit is valid, or else drop it in favor of current unit, or default unit (potentially nothing)
					this.unit = _isUnitLegal( potentialUnit, this.options.units ) ? potentialUnit : this.options.defaultUnit;
				}

				var intVal = this.getIntValue( val );

				//make sure we are dealing with a number
				if ( isNaN( intVal ) && !isFinite( intVal ) ) {
					return this._setValue( this.options.value, shouldSetLastValue );
				}

				//conform
				intVal = _applyLimits.call( this, intVal );

				//cache the pure int value
				this.options.value = intVal;

				//prepare number for display
				val = intVal + this.unit;

				if ( this.options.decimalMark !== '.' ) {
					val = ( val + '' ).split( '.' ).join( this.options.decimalMark );
				}

				//display number
				this.setDisplayValue( val );

				if ( shouldSetLastValue ) {
					this.lastValue = val;
				}

				return this;
			},

			value: function value( val ) {
				if ( val || val === 0 ) {
					return this.setValue( val );
				} else {
					return this.getValue();
				}
			},

			parseInput: function parseInput( value ) {
				value = ( value + '' ).split( this.options.decimalMark ).join( '.' );

				return value;
			},

			getIntValue: function getIntValue( value ) {
				//if they didn't pass in a number, try and get the number
				value = ( typeof value === "undefined" ) ? this.getValue() : value;
				// if there still isn't a number, abort
				if ( typeof value === "undefined" ) {
					return;
				}

				if ( typeof value === 'string' ) {
					value = this.parseInput( value );
				}

				value = parseFloat( value, 10 );

				return value;
			},

			disable: function disable() {
				this.options.disabled = true;
				this.$element.addClass( 'disabled' );
				this.$input.attr( 'disabled', '' );
				this.$element.find( 'button' ).addClass( 'disabled' );
			},

			enable: function enable() {
				this.options.disabled = false;
				this.$element.removeClass( 'disabled' );
				this.$input.removeAttr( 'disabled' );
				this.$element.find( 'button' ).removeClass( 'disabled' );
			},

			keydown: function keydown( event ) {
				var keyCode = event.keyCode;
				if ( keyCode === 38 ) {
					this.step( true );
				} else if ( keyCode === 40 ) {
					this.step( false );
				} else if ( keyCode === 13 ) {
					this.change();
				}
			},

			keyup: function keyup( event ) {
				var keyCode = event.keyCode;

				if ( keyCode === 38 || keyCode === 40 ) {
					this.triggerChangedEvent();
				}
			}

		};


		// SPINBOX PLUGIN DEFINITION

		$.fn.spinbox = function spinbox( option ) {
			var args = Array.prototype.slice.call( arguments, 1 );
			var methodReturn;

			var $set = this.each( function() {
				var $this = $( this );
				var data = $this.data( 'fu.spinbox' );
				var options = typeof option === 'object' && option;

				if ( !data ) {
					$this.data( 'fu.spinbox', ( data = new Spinbox( this, options ) ) );
				}

				if ( typeof option === 'string' ) {
					methodReturn = data[ option ].apply( data, args );
				}
			} );

			return ( methodReturn === undefined ) ? $set : methodReturn;
		};

		// value needs to be 0 for this.render();
		$.fn.spinbox.defaults = {
			value: 0,
			min: 0,
			max: 999,
			step: 1,
			hold: true,
			speed: 'medium',
			disabled: false,
			cycle: false,
			units: [],
			decimalMark: '.',
			defaultUnit: '',
			limitToStep: false
		};

		$.fn.spinbox.Constructor = Spinbox;

		$.fn.spinbox.noConflict = function noConflict() {
			$.fn.spinbox = old;
			return this;
		};


		// DATA-API

		$( document ).on( 'mousedown.fu.spinbox.data-api', '[data-initialize=spinbox]', function( e ) {
			var $control = $( e.target ).closest( '.spinbox' );
			if ( !$control.data( 'fu.spinbox' ) ) {
				$control.spinbox( $control.data() );
			}
		} );

		// Must be domReady for AMD compatibility
		$( function() {
			$( '[data-initialize=spinbox]' ).each( function() {
				var $this = $( this );
				if ( !$this.data( 'fu.spinbox' ) ) {
					$this.spinbox( $this.data() );
				}
			} );
		} );



	} )( jQuery );


	( function( $ ) {

		/* global jQuery:true */

		/*
		 * Fuel UX Tree
		 * https://github.com/ExactTarget/fuelux
		 *
		 * Copyright (c) 2014 ExactTarget
		 * Licensed under the BSD New license.
		 */



		// -- BEGIN MODULE CODE HERE --

		var old = $.fn.tree;

		// TREE CONSTRUCTOR AND PROTOTYPE

		var Tree = function Tree( element, options ) {
			this.$element = $( element );
			this.options = $.extend( {}, $.fn.tree.defaults, options );

			this.$element.attr( 'tabindex', '0' );

			if ( this.options.itemSelect ) {
				this.$element.on( 'click.fu.tree', '.tree-item', $.proxy( function callSelect( ev ) {
					this.selectItem( ev.currentTarget );
				}, this ) );
			}

			this.$element.on( 'click.fu.tree', '.tree-branch-name', $.proxy( function callToggle( ev ) {
				this.toggleFolder( ev.currentTarget );
			}, this ) );

			this.$element.on( 'click.fu.tree', '.tree-overflow', $.proxy( function callPopulate( ev ) {
				this.populate( $( ev.currentTarget ) );
			}, this ) );

			// folderSelect default is true
			if ( this.options.folderSelect ) {
				this.$element.addClass( 'tree-folder-select' );
				this.$element.off( 'click.fu.tree', '.tree-branch-name' );
				this.$element.on( 'click.fu.tree', '.icon-caret', $.proxy( function callToggle( ev ) {
					this.toggleFolder( $( ev.currentTarget ).parent() );
				}, this ) );
				this.$element.on( 'click.fu.tree', '.tree-branch-name', $.proxy( function callSelect( ev ) {
					this.selectFolder( $( ev.currentTarget ) );
				}, this ) );
			}

			this.$element.on( 'focus', function setFocusOnTab() {
				var $tree = $( this );
				focusIn( $tree, $tree );
			} );

			this.$element.on( 'keydown', function processKeypress( e ) {
				return navigateTree( $( this ), e );
			} );

			this.render();
		};

		Tree.prototype = {
			constructor: Tree,

			deselectAll: function deselectAll( n ) {
				// clear all child tree nodes and style as deselected
				var nodes = n || this.$element;
				var $selectedElements = $( nodes ).find( '.tree-selected' );
				$selectedElements.each( function callStyleNodeDeselected( index, element ) {
					var $element = $( element );
					ariaDeselect( $element );
					styleNodeDeselected( $element, $element.find( '.glyphicon' ) );
				} );
				return $selectedElements;
			},

			destroy: function destroy() {
				// any external bindings [none]
				// empty elements to return to original markup
				this.$element.find( 'li:not([data-template])' ).remove();

				this.$element.remove();
				// returns string of markup
				return this.$element[ 0 ].outerHTML;
			},

			render: function render() {
				this.populate( this.$element );
			},

			populate: function populate( $el, ibp ) {
				var self = this;

				// populate was initiated based on clicking overflow link
				var isOverflow = $el.hasClass( 'tree-overflow' );

				var $parent = ( $el.hasClass( 'tree' ) ) ? $el : $el.parent();
				var atRoot = $parent.hasClass( 'tree' );

				if ( isOverflow && !atRoot ) {
					$parent = $parent.parent();
				}

				var treeData = $parent.data();
				// expose overflow data to datasource so it can be responded to appropriately.
				if ( isOverflow ) {
					treeData.overflow = $el.data();
				}

				var isBackgroundProcess = ibp || false; // no user affordance needed (ex.- "loading")

				if ( isOverflow ) {
					if ( atRoot ) {
						// the loader at the root level needs to continually replace the overflow trigger
						// otherwise, when loader is shown below, it will be the loader for the last folder
						// in the tree, instead of the loader at the root level.
						$el.replaceWith( $parent.find( '> .tree-loader' ).remove() );
					} else {
						$el.remove();
					}
				}

				var $loader = $parent.find( '.tree-loader:last' );

				if ( isBackgroundProcess === false ) {
					$loader.removeClass( 'hide hidden' ); // jQuery deprecated hide in 3.0. Use hidden instead. Leaving hide here to support previous markup
				}

				this.options.dataSource( treeData ? treeData : {}, function populateNodes( items ) {
					$.each( items.data, function buildNode( i, treeNode ) {
						var nodeType = treeNode.type;

						// 'item' and 'overflow' remain consistent, but 'folder' maps to 'branch'
						if ( treeNode.type === 'folder' ) {
							nodeType = 'branch';
						}

						var $entity = self.$element
							.find( '[data-template=tree' + nodeType + ']:eq(0)' )
							.clone()
							.removeClass( 'hide hidden' ) // jQuery deprecated hide in 3.0. Use hidden instead. Leaving hide here to support previous markup
							.removeData( 'template' )
							.removeAttr( 'data-template' );
						$entity.find( '.tree-' + nodeType + '-name > .tree-label' ).html( treeNode.text || treeNode.name );
						$entity.data( treeNode );


						// Decorate $entity with data or other attributes making the
						// element easily accessible with libraries like jQuery.
						//
						// Values are contained within the object returned
						// for folders and items as attr:
						//
						// {
						//     text: "An Item",
						//     type: 'item',
						//     attr = {
						//         'classes': 'required-item red-text',
						//         'data-parent': parentId,
						//         'guid': guid,
						//         'id': guid
						//     }
						// };
						//
						// the "name" attribute is also supported but is deprecated for "text".

						// add attributes to tree-branch or tree-item
						var attrs = treeNode.attr || treeNode.dataAttributes || [];
						$.each( attrs, function setAttribute( attr, setTo ) {
							switch ( attr ) {
								case 'cssClass':
								case 'class':
								case 'className':
									$entity.addClass( setTo );
									break;

									// allow custom icons
								case 'data-icon':
									$entity.find( '.icon-item' ).removeClass().addClass( 'icon-item ' + setTo );
									$entity.attr( attr, setTo );
									break;

									// ARIA support
								case 'id':
									$entity.attr( attr, setTo );
									$entity.attr( 'aria-labelledby', setTo + '-label' );
									$entity.find( '.tree-branch-name > .tree-label' ).attr( 'id', setTo + '-label' );
									break;

									// style, data-*
								default:
									$entity.attr( attr, setTo );
									break;
							}
						} );

						// add child node
						if ( atRoot ) {
							// For accessibility reasons, the root element is the only tab-able element (see https://github.com/ExactTarget/fuelux/issues/1964)
							$parent.append( $entity );
						} else {
							$parent.find( '.tree-branch-children:eq(0)' ).append( $entity );
						}
					} );

					$parent.find( '.tree-loader' ).addClass( 'hidden' );
					// return newly populated folder
					self.$element.trigger( 'loaded.fu.tree', $parent );
				} );
			},

			selectTreeNode: function selectItem( clickedElement, nodeType , selectType) {
				var clicked = {}; // object for clicked element
				selectType = selectType || (this.options.toggleSelect? 'toggle' : 'select');
				clicked.$element = $( clickedElement );

				var selected = {}; // object for selected elements
				selected.$elements = this.$element.find( '.tree-selected' );
				selected.dataForEvent = [];

				// determine clicked element and it's icon
				if ( nodeType === 'folder' ) {
					// make the clicked.$element the container branch
					clicked.$element = clicked.$element.closest( '.tree-branch' );
					clicked.$icon = clicked.$element.find( '.icon-folder' );
				} else {
					clicked.$icon = clicked.$element.find( '.icon-item' );
				}
				clicked.elementData = clicked.$element.data();

				ariaSelect( clicked.$element );

				// the below functions pass objects by copy/reference and use modified object in this function
				if ( this.options.multiSelect ) {
					selected = multiSelectSyncNodes( this, clicked, selected );
				} else {
					selected = singleSelectSyncNodes( this, clicked, selected, selectType );
				}

				setFocus( this.$element, clicked.$element );

				if (selected.eventType) {
					// all done with the DOM, now fire events
					this.$element.trigger( selected.eventType + '.fu.tree', {
						target: clicked.elementData,
						selected: selected.dataForEvent
					} );

					clicked.$element.trigger( 'updated.fu.tree', {
						selected: selected.dataForEvent,
						item: clicked.$element,
						eventType: selected.eventType
					} );
				}
			},

			discloseFolder: function discloseFolder( folder ) {
				var $folder = $( folder );

				var $branch = $folder.closest( '.tree-branch' );
				var $treeFolderContent = $branch.find( '.tree-branch-children' );
				var $treeFolderContentFirstChild = $treeFolderContent.eq( 0 );

				// take care of the styles
				$branch.addClass( 'tree-open' );
				$branch.attr( 'aria-expanded', 'true' );
				$treeFolderContentFirstChild.removeClass( 'hide hidden' ); // jQuery deprecated hide in 3.0. Use hidden instead. Leaving hide here to support previous markup
				$branch.find( '> .tree-branch-header .icon-folder' ).eq( 0 )
					.removeClass( 'glyphicon-folder-close' )
					.addClass( 'glyphicon-folder-open' );

				var $tree = this.$element;
				var disclosedCompleted = function disclosedCompleted() {
					$tree.trigger( 'disclosedFolder.fu.tree', $branch.data() );
				};

				// add the children to the folder
				if ( !$treeFolderContent.children().length ) {
					$tree.one( 'loaded.fu.tree', disclosedCompleted );
					this.populate( $treeFolderContent );
				} else {
					disclosedCompleted();
				}
			},

			closeFolder: function closeFolder( el ) {
				var $el = $( el );
				var $branch = $el.closest( '.tree-branch' );
				var $treeFolderContent = $branch.find( '.tree-branch-children' );
				var $treeFolderContentFirstChild = $treeFolderContent.eq( 0 );

				// take care of the styles
				$branch.removeClass( 'tree-open' );
				$branch.attr( 'aria-expanded', 'false' );
				$treeFolderContentFirstChild.addClass( 'hidden' );
				$branch.find( '> .tree-branch-header .icon-folder' ).eq( 0 )
					.removeClass( 'glyphicon-folder-open' )
					.addClass( 'glyphicon-folder-close' );

				// remove childes if no cache
				if ( !this.options.cacheItems ) {
					$treeFolderContentFirstChild.empty();
				}

				this.$element.trigger( 'closed.fu.tree', $branch.data() );
			},

			toggleFolder: function toggleFolder( el ) {
				var $el = $( el );

				if ( $el.find( '.glyphicon-folder-close' ).length ) {
					this.discloseFolder( el );
				} else if ( $el.find( '.glyphicon-folder-open' ).length ) {
					this.closeFolder( el );
				}
			},

			selectFolder: function selectFolder( el, selectType) {
				if ( this.options.folderSelect ) {
					this.selectTreeNode( el, 'folder', selectType );
				}
			},

			selectItem: function selectItem( el, selectType ) {
				if ( this.options.itemSelect ) {
					this.selectTreeNode( el, 'item', selectType );
				}
			},

			selectedItems: function selectedItems() {
				var $sel = this.$element.find( '.tree-selected' );
				var selected = [];

				$.each( $sel, function buildSelectedArray( i, value ) {
					selected.push( $( value ).data() );
				} );
				return selected;
			},

			// collapses open folders
			collapse: function collapse() {
				var self = this;
				var reportedClosed = [];

				var closedReported = function closedReported( event, closed ) {
					reportedClosed.push( closed );

					// jQuery deprecated hide in 3.0. Use hidden instead. Leaving hide here to support previous markup
					if ( self.$element.find( ".tree-branch.tree-open:not('.hidden, .hide')" ).length === 0 ) {
						self.$element.trigger( 'closedAll.fu.tree', {
							tree: self.$element,
							reportedClosed: reportedClosed
						} );
						self.$element.off( 'loaded.fu.tree', self.$element, closedReported );
					}
				};

				// trigger callback when all folders have reported closed
				self.$element.on( 'closed.fu.tree', closedReported );

				self.$element.find( ".tree-branch.tree-open:not('.hidden, .hide')" ).each( function closeFolder() {
					self.closeFolder( this );
				} );
			},

			// disclose visible will only disclose visible tree folders
			discloseVisible: function discloseVisible() {
				var self = this;

				var $openableFolders = self.$element.find( ".tree-branch:not('.tree-open, .hidden, .hide')" );
				var reportedOpened = [];

				var openReported = function openReported( event, opened ) {
					reportedOpened.push( opened );

					if ( reportedOpened.length === $openableFolders.length ) {
						self.$element.trigger( 'disclosedVisible.fu.tree', {
							tree: self.$element,
							reportedOpened: reportedOpened
						} );
						/*
						 * Unbind the `openReported` event. `discloseAll` may be running and we want to reset this
						 * method for the next iteration.
						 */
						self.$element.off( 'loaded.fu.tree', self.$element, openReported );
					}
				};

				// trigger callback when all folders have reported opened
				self.$element.on( 'loaded.fu.tree', openReported );

				// open all visible folders
				self.$element.find( ".tree-branch:not('.tree-open, .hidden, .hide')" ).each( function triggerOpen() {
					self.discloseFolder( $( this ).find( '.tree-branch-header' ) );
				} );
			},

			/*
			 * Disclose all will keep listening for `loaded.fu.tree` and if `$(tree-el).data('ignore-disclosures-limit')`
			 * is `true` (defaults to `true`) it will attempt to disclose any new closed folders than were
			 * loaded in during the last disclosure.
			 */
			discloseAll: function discloseAll() {
				var self = this;

				// first time
				if ( typeof self.$element.data( 'disclosures' ) === 'undefined' ) {
					self.$element.data( 'disclosures', 0 );
				}

				var isExceededLimit = ( self.options.disclosuresUpperLimit >= 1 && self.$element.data( 'disclosures' ) >= self.options.disclosuresUpperLimit );
				var isAllDisclosed = self.$element.find( ".tree-branch:not('.tree-open, .hidden, .hide')" ).length === 0;


				if ( !isAllDisclosed ) {
					if ( isExceededLimit ) {
						self.$element.trigger( 'exceededDisclosuresLimit.fu.tree', {
							tree: self.$element,
							disclosures: self.$element.data( 'disclosures' )
						} );

						/*
						 * If you've exceeded the limit, the loop will be killed unless you
						 * explicitly ignore the limit and start the loop again:
						 *
						 *    $tree.one('exceededDisclosuresLimit.fu.tree', function () {
						 *        $tree.data('ignore-disclosures-limit', true);
						 *        $tree.tree('discloseAll');
						 *    });
						 */
						if ( !self.$element.data( 'ignore-disclosures-limit' ) ) {
							return;
						}
					}

					self.$element.data( 'disclosures', self.$element.data( 'disclosures' ) + 1 );

					/*
					 * A new branch that is closed might be loaded in, make sure those get handled too.
					 * This attachment needs to occur before calling `discloseVisible` to make sure that
					 * if the execution of `discloseVisible` happens _super fast_ (as it does in our QUnit tests
					 * this will still be called. However, make sure this only gets called _once_, because
					 * otherwise, every single time we go through this loop, _another_ event will be bound
					 * and then when the trigger happens, this will fire N times, where N equals the number
					 * of recursive `discloseAll` executions (instead of just one)
					 */
					self.$element.one( 'disclosedVisible.fu.tree', function callDiscloseAll() {
						self.discloseAll();
					} );

					/*
					 * If the page is very fast, calling this first will cause `disclosedVisible.fu.tree` to not
					 * be bound in time to be called, so, we need to call this last so that the things bound
					 * and triggered above can have time to take place before the next execution of the
					 * `discloseAll` method.
					 */
					self.discloseVisible();
				} else {
					self.$element.trigger( 'disclosedAll.fu.tree', {
						tree: self.$element,
						disclosures: self.$element.data( 'disclosures' )
					} );

					// if `cacheItems` is false, and they call closeAll, the data is trashed and therefore
					// disclosures needs to accurately reflect that
					if ( !self.options.cacheItems ) {
						self.$element.one( 'closeAll.fu.tree', function updateDisclosuresData() {
							self.$element.data( 'disclosures', 0 );
						} );
					}
				}
			},

			// This refreshes the "root level".
			refresh: function refresh() {
				this.$element.find( 'li:not([data-template])' ).remove();
				this.render();
			},

			// The data of the refreshed folder is not updated. This control's architecture only allows updating of children.
			// Folder renames should probably be handled directly on the node.
			refreshFolder: function refreshFolder( $el ) {
				var $treeFolder = $el.closest( '.tree-branch' );
				var $treeFolderChildren = $treeFolder.find( '.tree-branch-children' );
				$treeFolderChildren.eq( 0 ).empty();

				if ( $treeFolder.hasClass( 'tree-open' ) ) {
					this.populate( $treeFolderChildren, false );
				} else {
					this.populate( $treeFolderChildren, true );
				}

				this.$element.trigger( 'refreshedFolder.fu.tree', $treeFolder.data() );
			}

		};

		// ALIASES

		// alias for collapse for consistency. "Collapse" is an ambiguous term (collapse what? All? One specific branch?)
		Tree.prototype.closeAll = Tree.prototype.collapse;
		// alias for backwards compatibility because there's no reason not to.
		Tree.prototype.openFolder = Tree.prototype.discloseFolder;
		// For library consistency
		Tree.prototype.getValue = Tree.prototype.selectedItems;

		// PRIVATE FUNCTIONS

		var fixFocusability = function fixFocusability( $tree, $branch ) {
			/*
			When tree initializes on page, the `<ul>` element should have tabindex=0 and all sub-elements should have
			tabindex=-1. When focus leaves the tree, whatever the last focused on element was will keep the tabindex=0. The
			tree itself will have a tabindex=-1. The reason for this is that if you are inside of the tree and press
			shift+tab, it will try and focus on the tree you are already in, which will cause focus to shift immediately
			back to the element you are already focused on. That will make it seem like the event is getting "Swallowed up"
			by an aggressive event listener trap.

			For this reason, only one element in the entire tree, including the tree itself, should ever have tabindex=0.
			If somewhere down the line problems are being caused by this, the only further potential improvement I can
			envision at this time is listening for the tree to lose focus and reseting the tabindexes of all children to -1
			and setting the tabindex of the tree itself back to 0. This seems overly complicated with no benefit that I can
			imagine at this time, so instead I am leaving the last focused element with the tabindex of 0, even upon blur of
			the tree.

			One benefit to leaving the last focused element in a tree with a tabindex=0 is that if you accidentally tab out
			of the tree and then want to tab back in, you will be placed exactly where you left off instead of at the
			beginning of the tree.
			*/
			$tree.attr( 'tabindex', -1 );
			$tree.find( 'li' ).attr( 'tabindex', -1 );
			if ( $branch && $branch.length > 0 ) {
				$branch.attr( 'tabindex', 0 ); // if tabindex is not set to 0 (or greater), node is not able to receive focus
			}
		};

		// focuses into (onto one of the children of) the provided branch
		var focusIn = function focusIn( $tree, $branch ) {
			var $focusCandidate = $branch.find( '.tree-selected:first' );

			// if no node is selected, set focus to first visible node
			if ( $focusCandidate.length <= 0 ) {
				$focusCandidate = $branch.find( 'li:not(".hidden"):first' );
			}

			setFocus( $tree, $focusCandidate );
		};

		// focuses on provided branch
		var setFocus = function setFocus( $tree, $branch ) {
			fixFocusability( $tree, $branch );

			$tree.attr( 'aria-activedescendant', $branch.attr( 'id' ) );

			$branch.focus();

			$tree.trigger( 'setFocus.fu.tree', $branch );
		};

		var navigateTree = function navigateTree( $tree, e ) {
			if ( e.isDefaultPrevented() || e.isPropagationStopped() ) {
				return false;
			}

			var targetNode = e.originalEvent.target;
			var $targetNode = $( targetNode );
			var isOpen = $targetNode.hasClass( 'tree-open' );
			var handled = false;
			// because es5 lacks promises and fuelux has no polyfil (and I'm not adding one just for this change)
			// I am faking up promises here through callbacks and listeners. Done will be fired immediately at the end of
			// the navigateTree method if there is no (fake) promise, but will be fired by an event listener that will
			// be triggered by another function if necessary. This way when done runs, and fires keyboardNavigated.fu.tree
			// anything listening for that event can be sure that everything tied to that event is actually completed.
			var fireDoneImmediately = true;
			var done = function done() {
				$tree.trigger( 'keyboardNavigated.fu.tree', e, $targetNode );
			};

			switch ( e.which ) {
				case 13: // enter
				case 32: // space
					// activates a node, i.e., performs its default action.
					// For parent nodes, one possible default action is to open or close the node.
					// In single-select trees where selection does not follow focus, the default action is typically to select the focused node.
					var foldersSelectable = $tree.hasClass( 'tree-folder-select' );
					var isFolder = $targetNode.hasClass( 'tree-branch' );
					var isItem = $targetNode.hasClass( 'tree-item' );
					// var isOverflow = $targetNode.hasClass('tree-overflow');

					fireDoneImmediately = false;
					if ( isFolder ) {
						if ( foldersSelectable ) {
							$tree.one( 'selected.fu.tree deselected.fu.tree', done );
							$tree.tree( 'selectFolder', $targetNode.find( '.tree-branch-header' )[ 0 ] );
						} else {
							$tree.one( 'loaded.fu.tree closed.fu.tree', done );
							$tree.tree( 'toggleFolder', $targetNode.find( '.tree-branch-header' )[ 0 ] );
						}
					} else if ( isItem ) {
						$tree.one( 'selected.fu.tree', done );
						$tree.tree( 'selectItem', $targetNode );
					} else {
						// should be isOverflow... Try and click on it either way.
						$prev = $( $targetNode.prevAll().not( '.hidden' )[ 0 ] );
						$targetNode.click();

						$tree.one( 'loaded.fu.tree', function selectFirstNewlyLoadedNode() {
							$next = $( $prev.nextAll().not( '.hidden' )[ 0 ] );

							setFocus( $tree, $next );
							done();
						} );
					}

					handled = true;
					break;
				case 35: // end
					// Set focus to the last node in the tree that is focusable without opening a node.
					setFocus( $tree, $tree.find( 'li:not(".hidden"):last' ) );

					handled = true;
					break;
				case 36: // home
					// set focus to the first node in the tree without opening or closing a node.
					setFocus( $tree, $tree.find( 'li:not(".hidden"):first' ) );

					handled = true;
					break;
				case 37: // left
					if ( isOpen ) {
						fireDoneImmediately = false;
						$tree.one( 'closed.fu.tree', done );
						$tree.tree( 'closeFolder', targetNode );
					} else {
						setFocus( $tree, $( $targetNode.parents( 'li' )[ 0 ] ) );
					}

					handled = true;
					break;

				case 38: // up
					// move focus to previous sibling
					var $prev = [];
					// move to previous li not hidden
					$prev = $( $targetNode.prevAll().not( '.hidden' )[ 0 ] );

					// if the previous li is open, and has children, move selection to its last child so selection
					// appears to move to the next "thing" up
					if ( $prev.hasClass( 'tree-open' ) ) {
						var $prevChildren = $prev.find( 'li:not(".hidden"):last' );
						if ( $prevChildren.length > 0 ) {
							$prev = $( $prevChildren[ 0 ] );
						}
					}

					// if nothing has been selected, we are presumably at the top of an open li, select the immediate parent
					if ( $prev.length < 1 ) {
						$prev = $( $targetNode.parents( 'li' )[ 0 ] );
					}
					setFocus( $tree, $prev );

					handled = true;
					break;

				case 39: // right
					if ( isOpen ) {
						focusIn( $tree, $targetNode );
					} else {
						fireDoneImmediately = false;
						$tree.one( 'disclosed.fu.tree', done );
						$tree.tree( 'discloseFolder', targetNode );
					}

					handled = true;
					break;

				case 40: // down
					// move focus to next selectable tree node
					var $next = $( $targetNode.find( 'li:not(".hidden"):first' )[ 0 ] );
					if ( !isOpen || $next.length <= 0 ) {
						$next = $( $targetNode.nextAll().not( '.hidden' )[ 0 ] );
					}

					if ( $next.length < 1 ) {
						$next = $( $( $targetNode.parents( 'li' )[ 0 ] ).nextAll().not( '.hidden' )[ 0 ] );
					}
					setFocus( $tree, $next );

					handled = true;
					break;

				default:
					// console.log(e.which);
					return true; // exit this handler for other keys
			}

			// if we didn't handle the event, allow propagation to continue so something else might.
			if ( handled ) {
				e.preventDefault();
				e.stopPropagation();
				if ( fireDoneImmediately ) {
					done();
				}
			}

			return true;
		};

		var ariaSelect = function ariaSelect( $element ) {
			$element.attr( 'aria-selected', true );
		};

		var ariaDeselect = function ariaDeselect( $element ) {
			$element.attr( 'aria-selected', false );
		};

		function styleNodeSelected( $element, $icon ) {
			$element.addClass( 'tree-selected' );
			if ( $element.data( 'type' ) === 'item' && $icon.hasClass( 'fueluxicon-bullet' ) ) {
				$icon.removeClass( 'fueluxicon-bullet' ).addClass( 'glyphicon-ok' ); // make checkmark
			}
		}

		function styleNodeDeselected( $element, $icon ) {
			$element.removeClass( 'tree-selected' );
			if ( $element.data( 'type' ) === 'item' && $icon.hasClass( 'glyphicon-ok' ) ) {
				$icon.removeClass( 'glyphicon-ok' ).addClass( 'fueluxicon-bullet' ); // make bullet
			}
		}

		function multiSelectSyncNodes( self, clicked, selected ) {
			// search for currently selected and add to selected data list if needed
			$.each( selected.$elements, function findCurrentlySelected( index, element ) {
				var $element = $( element );

				if ( $element[ 0 ] !== clicked.$element[ 0 ] ) {
					selected.dataForEvent.push( $( $element ).data() );
				}
			} );

			if ( clicked.$element.hasClass( 'tree-selected' ) ) {
				styleNodeDeselected( clicked.$element, clicked.$icon );
				// set event data
				selected.eventType = 'deselected';
			} else {
				styleNodeSelected( clicked.$element, clicked.$icon );
				// set event data
				selected.eventType = 'selected';
				selected.dataForEvent.push( clicked.elementData );
			}

			return selected;
		}

		function singleSelectSyncNodes( self, clicked, selected , selectType) {
			// element is not currently selected
			if ( selected.$elements[ 0 ] !== clicked.$element[ 0 ] ) {
				switch (selectType) {
					case 'toggle':
					case 'select':
						self.deselectAll( self.$element );
						styleNodeSelected( clicked.$element, clicked.$icon );
						// set event data
						selected.eventType = 'selected';
						selected.dataForEvent = [ clicked.elementData ];
						break;
					default:
						//nothing
				}
			} else {
				switch (selectType) {
					case 'toggle':
					case 'deselect':
						styleNodeDeselected( clicked.$element, clicked.$icon );
						// set event data
						selected.eventType = 'deselected';
						selected.dataForEvent = [];
						break;
					default:
						//nothing
				}
			}

			return selected;
		}

		// TREE PLUGIN DEFINITION

		$.fn.tree = function fntree( option ) {
			var args = Array.prototype.slice.call( arguments, 1 );
			var methodReturn;

			var $set = this.each( function eachThis() {
				var $this = $( this );
				var data = $this.data( 'fu.tree' );
				var options = typeof option === 'object' && option;

				if ( !data ) {
					$this.data( 'fu.tree', ( data = new Tree( this, options ) ) );
					$this.trigger( 'initialized.fu.tree' );
				}

				if ( typeof option === 'string' ) {
					methodReturn = data[ option ].apply( data, args );
				}
			} );

			return ( methodReturn === undefined ) ? $set : methodReturn;
		};

		/*
		 * Private method used only by the default dataSource for the tree, which is used to consume static
		 * tree data.
		 *
		 * Find children of supplied parent in rootData. You can pass in an entire deeply nested tree
		 * and this will look through it recursively until it finds the child data you are looking for.
		 *
		 * For extremely large trees, this could cause the browser to crash, as there is no protection
		 * or limit on the amount of branches that will be searched through.
		 */
		var findChildData = function findChildData( targetParent, rootData ) {
			var isRootOfTree = $.isEmptyObject( targetParent );
			if ( isRootOfTree ) {
				return rootData;
			}

			if ( rootData === undefined ) {
				return false;
			}

			for ( var i = 0; i < rootData.length; i++ ) {
				var potentialMatch = rootData[ i ];

				if ( potentialMatch.attr && targetParent.attr && potentialMatch.attr.id === targetParent.attr.id ) {
					return potentialMatch.children;
				} else if ( potentialMatch.children ) {
					var foundChild = findChildData( targetParent, potentialMatch.children );
					if ( foundChild ) {
						return foundChild;
					}
				}
			}

			return false;
		};

		$.fn.tree.defaults = {
			/*
			 * A static data representation of your full tree data. If you do not override the tree's
			 * default dataSource method, this will just make the tree work out of the box without
			 * you having to bring your own dataSource.
			 *
			 * Array of Objects representing tree branches (folder) and leaves (item):
				[
					{
						name: '',
						type: 'folder',
						attr: {
							id: ''
						},
						children: [
							{
								name: '',
								type: 'item',
								attr: {
									id: '',
									'data-icon': 'glyphicon glyphicon-file'
								}
							}
						]
					},
					{
						name: '',
						type: 'item',
						attr: {
							id: '',
							'data-icon': 'glyphicon glyphicon-file'
						}
					}
				];
			 */
			staticData: [],
			/*
			 * If you set the full tree data on options.staticData, you can use this default dataSource
			 * to consume that data. This allows you to just pass in a JSON array representation
			 * of your full tree data and the tree will just work out of the box.
			 */
			dataSource: function staticDataSourceConsumer( openedParentData, callback ) {
				if ( this.staticData.length > 0 ) {
					var childData = findChildData( openedParentData, this.staticData );

					callback( {
						data: childData
					} );
				}
			},
			multiSelect: false,
			cacheItems: true,
			folderSelect: true,
			itemSelect: true,
			toggleSelect: true,
			/*
			 * How many times `discloseAll` should be called before a stopping and firing
			 * an `exceededDisclosuresLimit` event. You can force it to continue by
			 * listening for this event, setting `ignore-disclosures-limit` to `true` and
			 * starting `discloseAll` back up again. This lets you make more decisions
			 * about if/when/how/why/how many times `discloseAll` will be started back
			 * up after it exceeds the limit.
			 *
			 *    $tree.one('exceededDisclosuresLimit.fu.tree', function () {
			 *        $tree.data('ignore-disclosures-limit', true);
			 *        $tree.tree('discloseAll');
			 *    });
			 *
			 * `disclusuresUpperLimit` defaults to `0`, so by default this trigger
			 * will never fire. The true hard the upper limit is the browser's
			 * ability to load new items (i.e. it will keep loading until the browser
			 * falls over and dies). On the Fuel UX `index.html` page, the point at
			 * which the page became super slow (enough to seem almost unresponsive)
			 * was `4`, meaning 256 folders had been opened, and 1024 were attempting to open.
			 */
			disclosuresUpperLimit: 0
		};

		$.fn.tree.Constructor = Tree;

		$.fn.tree.noConflict = function noConflict() {
			$.fn.tree = old;
			return this;
		};


		// NO DATA-API DUE TO NEED OF DATA-SOURCE



	} )( jQuery );


	( function( $ ) {

		/* global jQuery:true */

		/*
		 * Fuel UX Utilities
		 * https://github.com/ExactTarget/fuelux
		 *
		 * Copyright (c) 2016 ExactTarget
		 * Licensed under the BSD New license.
		 */



		// -- BEGIN MODULE CODE HERE --
		var CONST = {
			BACKSPACE_KEYCODE: 8,
			COMMA_KEYCODE: 188, // `,` & `<`
			DELETE_KEYCODE: 46,
			DOWN_ARROW_KEYCODE: 40,
			ENTER_KEYCODE: 13,
			TAB_KEYCODE: 9,
			UP_ARROW_KEYCODE: 38
		};

		var isShiftHeld = function isShiftHeld( e ) {
			return e.shiftKey === true;
		};

		var isKey = function isKey( keyCode ) {
			return function compareKeycodes( e ) {
				return e.keyCode === keyCode;
			};
		};

		var isBackspaceKey = isKey( CONST.BACKSPACE_KEYCODE );
		var isDeleteKey = isKey( CONST.DELETE_KEYCODE );
		var isTabKey = isKey( CONST.TAB_KEYCODE );
		var isUpArrow = isKey( CONST.UP_ARROW_KEYCODE );
		var isDownArrow = isKey( CONST.DOWN_ARROW_KEYCODE );

		var ENCODED_REGEX = /&[^\s]*;/;
		/*
		 * to prevent double encoding decodes content in loop until content is encoding free
		 */
		var cleanInput = function cleanInput( questionableMarkup ) {
			// check for encoding and decode
			while ( ENCODED_REGEX.test( questionableMarkup ) ) {
				questionableMarkup = $( '<i>' ).html( questionableMarkup ).text();
			}

			// string completely decoded now encode it
			return $( '<i>' ).text( questionableMarkup ).html();
		};

		$.fn.utilities = {
			CONST: CONST,
			cleanInput: cleanInput,
			isBackspaceKey: isBackspaceKey,
			isDeleteKey: isDeleteKey,
			isShiftHeld: isShiftHeld,
			isTabKey: isTabKey,
			isUpArrow: isUpArrow,
			isDownArrow: isDownArrow
		};



	} )( jQuery );


	( function( $ ) {

		/* global jQuery:true */

		/*
		 * Fuel UX Wizard
		 * https://github.com/ExactTarget/fuelux
		 *
		 * Copyright (c) 2014 ExactTarget
		 * Licensed under the BSD New license.
		 */



		// -- BEGIN MODULE CODE HERE --

		var old = $.fn.wizard;

		// WIZARD CONSTRUCTOR AND PROTOTYPE

		var Wizard = function( element, options ) {
			this.$element = $( element );
			this.options = $.extend( {}, $.fn.wizard.defaults, options );
			this.options.disablePreviousStep = ( this.$element.attr( 'data-restrict' ) === 'previous' ) ? true : this.options.disablePreviousStep;
			this.currentStep = this.options.selectedItem.step;
			this.numSteps = this.$element.find( '.steps li' ).length;
			this.$prevBtn = this.$element.find( 'button.btn-prev' );
			this.$nextBtn = this.$element.find( 'button.btn-next' );

			var kids = this.$nextBtn.children().detach();
			this.nextText = $.trim( this.$nextBtn.text() );
			this.$nextBtn.append( kids );

			var steps = this.$element.children( '.steps-container' );
			// maintains backwards compatibility with < 3.8, will be removed in the future
			if ( steps.length === 0 ) {
				steps = this.$element;
				this.$element.addClass( 'no-steps-container' );
				if ( window && window.console && window.console.warn ) {
					window.console.warn( 'please update your wizard markup to include ".steps-container" as seen in http://getfuelux.com/javascript.html#wizard-usage-markup' );
				}
			}
			steps = steps.find( '.steps' );

			// handle events
			this.$prevBtn.on( 'click.fu.wizard', $.proxy( this.previous, this ) );
			this.$nextBtn.on( 'click.fu.wizard', $.proxy( this.next, this ) );
			steps.on( 'click.fu.wizard', 'li.complete', $.proxy( this.stepclicked, this ) );

			this.selectedItem( this.options.selectedItem );

			if ( this.options.disablePreviousStep ) {
				this.$prevBtn.attr( 'disabled', true );
				this.$element.find( '.steps' ).addClass( 'previous-disabled' );
			}
		};

		Wizard.prototype = {

			constructor: Wizard,

			destroy: function() {
				this.$element.remove();
				// any external bindings [none]
				// empty elements to return to original markup [none]
				// returns string of markup
				return this.$element[ 0 ].outerHTML;
			},

			//index is 1 based
			//second parameter can be array of objects [{ ... }, { ... }] or you can pass n additional objects as args
			//object structure is as follows (all params are optional): { badge: '', label: '', pane: '' }
			addSteps: function( index ) {
				var items = [].slice.call( arguments ).slice( 1 );
				var $steps = this.$element.find( '.steps' );
				var $stepContent = this.$element.find( '.step-content' );
				var i, l, $pane, $startPane, $startStep, $step;

				index = ( index === -1 || ( index > ( this.numSteps + 1 ) ) ) ? this.numSteps + 1 : index;
				if ( items[ 0 ] instanceof Array ) {
					items = items[ 0 ];
				}

				$startStep = $steps.find( 'li:nth-child(' + index + ')' );
				$startPane = $stepContent.find( '.step-pane:nth-child(' + index + ')' );
				if ( $startStep.length < 1 ) {
					$startStep = null;
				}

				for ( i = 0, l = items.length; i < l; i++ ) {
					$step = $( '<li data-step="' + index + '"><span class="badge badge-info"></span></li>' );
					$step.append( items[ i ].label || '' ).append( '<span class="chevron"></span>' );
					$step.find( '.badge' ).append( items[ i ].badge || index );

					$pane = $( '<div class="step-pane" data-step="' + index + '"></div>' );
					$pane.append( items[ i ].pane || '' );

					if ( !$startStep ) {
						$steps.append( $step );
						$stepContent.append( $pane );
					} else {
						$startStep.before( $step );
						$startPane.before( $pane );
					}

					index++;
				}

				this.syncSteps();
				this.numSteps = $steps.find( 'li' ).length;
				this.setState();
			},

			//index is 1 based, howMany is number to remove
			removeSteps: function( index, howMany ) {
				var action = 'nextAll';
				var i = 0;
				var $steps = this.$element.find( '.steps' );
				var $stepContent = this.$element.find( '.step-content' );
				var $start;

				howMany = ( howMany !== undefined ) ? howMany : 1;

				if ( index > $steps.find( 'li' ).length ) {
					$start = $steps.find( 'li:last' );
				} else {
					$start = $steps.find( 'li:nth-child(' + index + ')' ).prev();
					if ( $start.length < 1 ) {
						action = 'children';
						$start = $steps;
					}

				}

				$start[ action ]().each( function() {
					var item = $( this );
					var step = item.attr( 'data-step' );
					if ( i < howMany ) {
						item.remove();
						$stepContent.find( '.step-pane[data-step="' + step + '"]:first' ).remove();
					} else {
						return false;
					}

					i++;
				} );

				this.syncSteps();
				this.numSteps = $steps.find( 'li' ).length;
				this.setState();
			},

			setState: function() {
				var canMovePrev = ( this.currentStep > 1 ); //remember, steps index is 1 based...
				var isFirstStep = ( this.currentStep === 1 );
				var isLastStep = ( this.currentStep === this.numSteps );

				// disable buttons based on current step
				if ( !this.options.disablePreviousStep ) {
					this.$prevBtn.attr( 'disabled', ( isFirstStep === true || canMovePrev === false ) );
				}

				// change button text of last step, if specified
				var last = this.$nextBtn.attr( 'data-last' );
				if ( last ) {
					this.lastText = last;
					// replace text
					var text = this.nextText;
					if ( isLastStep === true ) {
						text = this.lastText;
						// add status class to wizard
						this.$element.addClass( 'complete' );
					} else {
						this.$element.removeClass( 'complete' );
					}

					var kids = this.$nextBtn.children().detach();
					this.$nextBtn.text( text ).append( kids );
				}

				// reset classes for all steps
				var $steps = this.$element.find( '.steps li' );
				$steps.removeClass( 'active' ).removeClass( 'complete' );
				$steps.find( 'span.badge' ).removeClass( 'badge-info' ).removeClass( 'badge-success' );

				// set class for all previous steps
				var prevSelector = '.steps li:lt(' + ( this.currentStep - 1 ) + ')';
				var $prevSteps = this.$element.find( prevSelector );
				$prevSteps.addClass( 'complete' );
				$prevSteps.find( 'span.badge' ).addClass( 'badge-success' );

				// set class for current step
				var currentSelector = '.steps li:eq(' + ( this.currentStep - 1 ) + ')';
				var $currentStep = this.$element.find( currentSelector );
				$currentStep.addClass( 'active' );
				$currentStep.find( 'span.badge' ).addClass( 'badge-info' );

				// set display of target element
				var $stepContent = this.$element.find( '.step-content' );
				var target = $currentStep.attr( 'data-step' );
				$stepContent.find( '.step-pane' ).removeClass( 'active' );
				$stepContent.find( '.step-pane[data-step="' + target + '"]:first' ).addClass( 'active' );

				// reset the wizard position to the left
				this.$element.find( '.steps' ).first().attr( 'style', 'margin-left: 0' );

				// check if the steps are wider than the container div
				var totalWidth = 0;
				this.$element.find( '.steps > li' ).each( function() {
					totalWidth += $( this ).outerWidth();
				} );
				var containerWidth = 0;
				if ( this.$element.find( '.actions' ).length ) {
					containerWidth = this.$element.width() - this.$element.find( '.actions' ).first().outerWidth();
				} else {
					containerWidth = this.$element.width();
				}

				if ( totalWidth > containerWidth ) {
					// set the position so that the last step is on the right
					var newMargin = totalWidth - containerWidth;
					this.$element.find( '.steps' ).first().attr( 'style', 'margin-left: -' + newMargin + 'px' );

					// set the position so that the active step is in a good
					// position if it has been moved out of view
					if ( this.$element.find( 'li.active' ).first().position().left < 200 ) {
						newMargin += this.$element.find( 'li.active' ).first().position().left - 200;
						if ( newMargin < 1 ) {
							this.$element.find( '.steps' ).first().attr( 'style', 'margin-left: 0' );
						} else {
							this.$element.find( '.steps' ).first().attr( 'style', 'margin-left: -' + newMargin + 'px' );
						}

					}

				}

				// only fire changed event after initializing
				if ( typeof( this.initialized ) !== 'undefined' ) {
					var e = $.Event( 'changed.fu.wizard' );
					this.$element.trigger( e, {
						step: this.currentStep
					} );
				}

				this.initialized = true;
			},

			stepclicked: function( e ) {
				var li = $( e.currentTarget );
				var index = this.$element.find( '.steps li' ).index( li );

				if ( index < this.currentStep && this.options.disablePreviousStep ) { //enforce restrictions
					return;
				} else {
					var evt = $.Event( 'stepclicked.fu.wizard' );
					this.$element.trigger( evt, {
						step: index + 1
					} );
					if ( evt.isDefaultPrevented() ) {
						return;
					}

					this.currentStep = ( index + 1 );
					this.setState();
				}
			},

			syncSteps: function() {
				var i = 1;
				var $steps = this.$element.find( '.steps' );
				var $stepContent = this.$element.find( '.step-content' );

				$steps.children().each( function() {
					var item = $( this );
					var badge = item.find( '.badge' );
					var step = item.attr( 'data-step' );

					if ( !isNaN( parseInt( badge.html(), 10 ) ) ) {
						badge.html( i );
					}

					item.attr( 'data-step', i );
					$stepContent.find( '.step-pane[data-step="' + step + '"]:last' ).attr( 'data-step', i );
					i++;
				} );
			},

			previous: function() {
				if ( this.options.disablePreviousStep || this.currentStep === 1 ) {
					return;
				}

				var e = $.Event( 'actionclicked.fu.wizard' );
				this.$element.trigger( e, {
					step: this.currentStep,
					direction: 'previous'
				} );
				if ( e.isDefaultPrevented() ) {
					return;
				} // don't increment ...what? Why?

				this.currentStep -= 1;
				this.setState();

				// only set focus if focus is still on the $nextBtn (avoid stomping on a focus set programmatically in actionclicked callback)
				if ( this.$prevBtn.is( ':focus' ) ) {
					var firstFormField = this.$element.find( '.active' ).find( 'input, select, textarea' )[ 0 ];

					if ( typeof firstFormField !== 'undefined' ) {
						// allow user to start typing immediately instead of having to click on the form field.
						$( firstFormField ).focus();
					} else if ( this.$element.find( '.active input:first' ).length === 0 && this.$prevBtn.is( ':disabled' ) ) {
						//only set focus on a button as the last resort if no form fields exist and the just clicked button is now disabled
						this.$nextBtn.focus();
					}

				}
			},

			next: function() {
				var e = $.Event( 'actionclicked.fu.wizard' );
				this.$element.trigger( e, {
					step: this.currentStep,
					direction: 'next'
				} );
				if ( e.isDefaultPrevented() ) {
					return;
				} // respect preventDefault in case dev has attached validation to step and wants to stop propagation based on it.

				if ( this.currentStep < this.numSteps ) {
					this.currentStep += 1;
					this.setState();
				} else { //is last step
					this.$element.trigger( 'finished.fu.wizard' );
				}

				// only set focus if focus is still on the $nextBtn (avoid stomping on a focus set programmatically in actionclicked callback)
				if ( this.$nextBtn.is( ':focus' ) ) {
					var firstFormField = this.$element.find( '.active' ).find( 'input, select, textarea' )[ 0 ];

					if ( typeof firstFormField !== 'undefined' ) {
						// allow user to start typing immediately instead of having to click on the form field.
						$( firstFormField ).focus();
					} else if ( this.$element.find( '.active input:first' ).length === 0 && this.$nextBtn.is( ':disabled' ) ) {
						//only set focus on a button as the last resort if no form fields exist and the just clicked button is now disabled
						this.$prevBtn.focus();
					}

				}
			},

			selectedItem: function( selectedItem ) {
				var retVal, step;

				if ( selectedItem ) {
					step = selectedItem.step || -1;
					//allow selection of step by data-name
					step = Number( this.$element.find( '.steps li[data-name="' + step + '"]' ).first().attr( 'data-step' ) ) || Number( step );

					if ( 1 <= step && step <= this.numSteps ) {
						this.currentStep = step;
						this.setState();
					} else {
						step = this.$element.find( '.steps li.active:first' ).attr( 'data-step' );
						if ( !isNaN( step ) ) {
							this.currentStep = parseInt( step, 10 );
							this.setState();
						}

					}

					retVal = this;
				} else {
					retVal = {
						step: this.currentStep
					};
					if ( this.$element.find( '.steps li.active:first[data-name]' ).length ) {
						retVal.stepname = this.$element.find( '.steps li.active:first' ).attr( 'data-name' );
					}

				}

				return retVal;
			}
		};


		// WIZARD PLUGIN DEFINITION

		$.fn.wizard = function( option ) {
			var args = Array.prototype.slice.call( arguments, 1 );
			var methodReturn;

			var $set = this.each( function() {
				var $this = $( this );
				var data = $this.data( 'fu.wizard' );
				var options = typeof option === 'object' && option;

				if ( !data ) {
					$this.data( 'fu.wizard', ( data = new Wizard( this, options ) ) );
				}

				if ( typeof option === 'string' ) {
					methodReturn = data[ option ].apply( data, args );
				}
			} );

			return ( methodReturn === undefined ) ? $set : methodReturn;
		};

		$.fn.wizard.defaults = {
			disablePreviousStep: false,
			selectedItem: {
				step: -1
			} //-1 means it will attempt to look for "active" class in order to set the step
		};

		$.fn.wizard.Constructor = Wizard;

		$.fn.wizard.noConflict = function() {
			$.fn.wizard = old;
			return this;
		};


		// DATA-API

		$( document ).on( 'mouseover.fu.wizard.data-api', '[data-initialize=wizard]', function( e ) {
			var $control = $( e.target ).closest( '.wizard' );
			if ( !$control.data( 'fu.wizard' ) ) {
				$control.wizard( $control.data() );
			}
		} );

		// Must be domReady for AMD compatibility
		$( function() {
			$( '[data-initialize=wizard]' ).each( function() {
				var $this = $( this );
				if ( $this.data( 'fu.wizard' ) ) return;
				$this.wizard( $this.data() );
			} );
		} );



	} )( jQuery );


	( function( $ ) {

		/* global jQuery:true */

		/*
		 * Fuel UX Infinite Scroll
		 * https://github.com/ExactTarget/fuelux
		 *
		 * Copyright (c) 2014 ExactTarget
		 * Licensed under the BSD New license.
		 */



		// -- BEGIN MODULE CODE HERE --

		var old = $.fn.infinitescroll;

		// INFINITE SCROLL CONSTRUCTOR AND PROTOTYPE

		var InfiniteScroll = function( element, options ) {
			this.$element = $( element );
			this.$element.addClass( 'infinitescroll' );
			this.options = $.extend( {}, $.fn.infinitescroll.defaults, options );

			this.curScrollTop = this.$element.scrollTop();
			this.curPercentage = this.getPercentage();
			this.fetchingData = false;

			this.$element.on( 'scroll.fu.infinitescroll', $.proxy( this.onScroll, this ) );
			this.onScroll();
		};

		InfiniteScroll.prototype = {

			constructor: InfiniteScroll,

			destroy: function() {
				this.$element.remove();
				// any external bindings
				// [none]

				// empty elements to return to original markup
				this.$element.empty();

				return this.$element[ 0 ].outerHTML;
			},

			disable: function() {
				this.$element.off( 'scroll.fu.infinitescroll' );
			},

			enable: function() {
				this.$element.on( 'scroll.fu.infinitescroll', $.proxy( this.onScroll, this ) );
			},

			end: function( content ) {
				var end = $( '<div class="infinitescroll-end"></div>' );
				if ( content ) {
					end.append( content );
				} else {
					end.append( '---------' );
				}

				this.$element.append( end );
				this.disable();
			},

			getPercentage: function() {
				var height = ( this.$element.css( 'box-sizing' ) === 'border-box' ) ? this.$element.outerHeight() : this.$element.height();
				var scrollHeight = this.$element.get( 0 ).scrollHeight;
				// If we cannot compute the height, then we end up fetching all pages (ends up #/0 = Infinity).
				// This can happen if the repeater is loaded, but is not in the dom
				if ( scrollHeight === 0 || scrollHeight - this.curScrollTop === 0 ) {
					return 0;
				}
				return ( height / ( scrollHeight - this.curScrollTop ) ) * 100;
			},

			fetchData: function( force ) {
				var load = $( '<div class="infinitescroll-load"></div>' );
				var self = this;
				var moreBtn;

				var fetch = function() {
					var helpers = {
						percentage: self.curPercentage,
						scrollTop: self.curScrollTop
					};
					var $loader = $( '<div class="loader"></div>' );
					load.append( $loader );
					$loader.loader();
					if ( self.options.dataSource ) {
						self.options.dataSource( helpers, function( resp ) {
							var end;
							load.remove();
							if ( resp.content ) {
								self.$element.append( resp.content );
							}

							if ( resp.end ) {
								end = ( resp.end !== true ) ? resp.end : undefined;
								self.end( end );
							}

							self.fetchingData = false;
						} );
					}
				};

				this.fetchingData = true;
				this.$element.append( load );
				if ( this.options.hybrid && force !== true ) {
					moreBtn = $( '<button type="button" class="btn btn-primary"></button>' );
					if ( typeof this.options.hybrid === 'object' ) {
						moreBtn.append( this.options.hybrid.label );
					} else {
						moreBtn.append( '<span class="glyphicon glyphicon-repeat"></span>' );
					}

					moreBtn.on( 'click.fu.infinitescroll', function() {
						moreBtn.remove();
						fetch();
					} );
					load.append( moreBtn );
				} else {
					fetch();
				}
			},

			onScroll: function( e ) {
				this.curScrollTop = this.$element.scrollTop();
				this.curPercentage = this.getPercentage();
				if ( !this.fetchingData && this.curPercentage >= this.options.percentage ) {
					this.fetchData();
				}
			}
		};

		// INFINITE SCROLL PLUGIN DEFINITION

		$.fn.infinitescroll = function( option ) {
			var args = Array.prototype.slice.call( arguments, 1 );
			var methodReturn;

			var $set = this.each( function() {
				var $this = $( this );
				var data = $this.data( 'fu.infinitescroll' );
				var options = typeof option === 'object' && option;

				if ( !data ) {
					$this.data( 'fu.infinitescroll', ( data = new InfiniteScroll( this, options ) ) );
				}

				if ( typeof option === 'string' ) {
					methodReturn = data[ option ].apply( data, args );
				}
			} );

			return ( methodReturn === undefined ) ? $set : methodReturn;
		};

		$.fn.infinitescroll.defaults = {
			dataSource: null,
			hybrid: false, //can be true or an object with structure: { 'label': (markup or jQuery obj) }
			percentage: 95 //percentage scrolled to the bottom before more is loaded
		};

		$.fn.infinitescroll.Constructor = InfiniteScroll;

		$.fn.infinitescroll.noConflict = function() {
			$.fn.infinitescroll = old;
			return this;
		};

		// NO DATA-API DUE TO NEED OF DATA-SOURCE



	} )( jQuery );


	( function( $ ) {

		/* global jQuery:true utilities:true */

		/*
		 * Fuel UX Pillbox
		 * https://github.com/ExactTarget/fuelux
		 *
		 * Copyright (c) 2014 ExactTarget
		 * Licensed under the BSD New license.
		 */



		// -- BEGIN MODULE CODE HERE --

		var old = $.fn.pillbox;

		var utilities = $.fn.utilities;
		var CONST = $.fn.utilities.CONST;
		var COMMA_KEYCODE = CONST.COMMA_KEYCODE;
		var ENTER_KEYCODE = CONST.ENTER_KEYCODE;
		var isBackspaceKey = utilities.isBackspaceKey;
		var isDeleteKey = utilities.isDeleteKey;
		var isTabKey = utilities.isTabKey;
		var isUpArrow = utilities.isUpArrow;
		var isDownArrow = utilities.isDownArrow;
		var cleanInput = utilities.cleanInput;
		var isShiftHeld = utilities.isShiftHeld;

		// PILLBOX CONSTRUCTOR AND PROTOTYPE
		var Pillbox = function Pillbox( element, options ) {
			this.$element = $( element );
			this.$moreCount = this.$element.find( '.pillbox-more-count' );
			this.$pillGroup = this.$element.find( '.pill-group' );
			this.$addItem = this.$element.find( '.pillbox-add-item' );
			this.$addItemWrap = this.$addItem.parent();
			this.$suggest = this.$element.find( '.suggest' );
			this.$pillHTML = '<li class="btn btn-default pill">' +
				'	<span></span>' +
				'	<span class="glyphicon glyphicon-close">' +
				'		<span class="sr-only">Remove</span>' +
				'	</span>' +
				'</li>';

			this.options = $.extend( {}, $.fn.pillbox.defaults, options );

			if ( this.options.readonly === -1 ) {
				if ( this.$element.attr( 'data-readonly' ) !== undefined ) {
					this.readonly( true );
				}
			} else if ( this.options.readonly ) {
				this.readonly( true );
			}

			// EVENTS
			this.acceptKeyCodes = this._generateObject( this.options.acceptKeyCodes );
			// Create an object out of the key code array, so we don't have to loop through it on every key stroke

			this.$element.on( 'click.fu.pillbox', '.pill-group > .pill', $.proxy( this.itemClicked, this ) );
			this.$element.on( 'click.fu.pillbox', $.proxy( this.inputFocus, this ) );
			this.$element.on( 'keydown.fu.pillbox', '.pillbox-add-item', $.proxy( this.inputEvent, this ) );
			if ( this.options.onKeyDown ) {
				this.$element.on( 'mousedown.fu.pillbox', '.suggest > li', $.proxy( this.suggestionClick, this ) );
			}

			if ( this.options.edit ) {
				this.$element.addClass( 'pills-editable' );
				this.$element.on( 'blur.fu.pillbox', '.pillbox-add-item', $.proxy( this.cancelEdit, this ) );
			}
			this.$element.on( 'blur.fu.pillbox', '.pillbox-add-item', $.proxy( this.inputEvent, this ) );
		};

		Pillbox.prototype = {
			constructor: Pillbox,

			destroy: function destroy() {
				this.$element.remove();
				// any external bindings
				// [none]
				// empty elements to return to original markup
				// [none]
				// returns string of markup
				return this.$element[ 0 ].outerHTML;
			},

			items: function items() {
				var self = this;

				return this.$pillGroup.children( '.pill' ).map( function getItemsData() {
					return self.getItemData( $( this ) );
				} ).get();
			},

			itemClicked: function itemClicked( e ) {
				var $target = $( e.target );
				var $item;

				e.preventDefault();
				e.stopPropagation();
				this._closeSuggestions();

				if ( !$target.hasClass( 'pill' ) ) {
					$item = $target.parent();
					if ( this.$element.attr( 'data-readonly' ) === undefined ) {
						if ( $target.hasClass( 'glyphicon-close' ) ) {
							if ( this.options.onRemove ) {
								this.options.onRemove( this.getItemData( $item, {
									el: $item
								} ), $.proxy( this._removeElement, this ) );
							} else {
								this._removeElement( this.getItemData( $item, {
									el: $item
								} ) );
							}

							return false;
						} else if ( this.options.edit ) {
							if ( $item.find( '.pillbox-list-edit' ).length ) {
								return false;
							}

							this.openEdit( $item );
						}
					}
				} else {
					$item = $target;
				}

				this.$element.trigger( 'clicked.fu.pillbox', this.getItemData( $item ) );

				return true;
			},

			readonly: function readonly( enable ) {
				if ( enable ) {
					this.$element.attr( 'data-readonly', 'readonly' );
				} else {
					this.$element.removeAttr( 'data-readonly' );
				}

				if ( this.options.truncate ) {
					this.truncate( enable );
				}
			},

			suggestionClick: function suggestionClick( e ) {
				var $item = $( e.currentTarget );
				var item = {
					text: $item.html(),
					value: $item.data( 'value' )
				};

				e.preventDefault();
				this.$addItem.val( '' );

				if ( $item.data( 'attr' ) ) {
					item.attr = JSON.parse( $item.data( 'attr' ) );
				}

				item.data = $item.data( 'data' );

				this.addItems( item, true );

				// needs to be after addItems for IE
				this._closeSuggestions();
			},

			itemCount: function itemCount() {
				return this.$pillGroup.children( '.pill' ).length;
			},

			// First parameter is 1 based index (optional, if index is not passed all new items will be appended)
			// Second parameter can be array of objects [{ ... }, { ... }] or you can pass n additional objects as args
			// object structure is as follows (attr and value are optional): { text: '', value: '', attr: {}, data: {} }
			addItems: function addItems() {
				var self = this;
				var items;
				var index;
				var isInternal;

				if ( isFinite( String( arguments[ 0 ] ) ) && !( arguments[ 0 ] instanceof Array ) ) {
					items = [].slice.call( arguments ).slice( 1 );
					index = arguments[ 0 ];
				} else {
					items = [].slice.call( arguments ).slice( 0 );
					isInternal = items[ 1 ] && !items[ 1 ].text;
				}

				// If first argument is an array, use that, otherwise they probably passed each thing through as a separate arg, so use items as-is
				if ( items[ 0 ] instanceof Array ) {
					items = items[ 0 ];
				}

				if ( items.length ) {
					$.each( items, function normalizeItemsObject( i, value ) {
						var data = {
							text: value.text,
							value: ( value.value ? value.value : value.text ),
							el: self.$pillHTML
						};

						if ( value.attr ) {
							data.attr = value.attr;
						}

						if ( value.data ) {
							data.data = value.data;
						}

						items[ i ] = data;
					} );

					if ( this.options.edit && this.currentEdit ) {
						items[ 0 ].el = this.currentEdit.wrap( '<div></div>' ).parent().html();
					}

					if ( isInternal ) {
						items.pop( 1 );
					}

					if ( self.options.onAdd && isInternal ) {
						if ( this.options.edit && this.currentEdit ) {
							self.options.onAdd( items[ 0 ], $.proxy( self.saveEdit, this ) );
						} else {
							self.options.onAdd( items[ 0 ], $.proxy( self.placeItems, this ) );
						}
					} else if ( this.options.edit && this.currentEdit ) {
						self.saveEdit( items );
					} else if ( index ) {
						self.placeItems( index, items );
					} else {
						self.placeItems( items, isInternal );
					}
				}
			},

			// First parameter is the index (1 based) to start removing items
			// Second parameter is the number of items to be removed
			removeItems: function removeItems( index, howMany ) {
				var self = this;

				if ( !index ) {
					this.$pillGroup.find( '.pill' ).remove();
					this._removePillTrigger( {
						method: 'removeAll'
					} );
				} else {
					var itemsToRemove = howMany ? howMany : 1;

					for ( var item = 0; item < itemsToRemove; item++ ) {
						var $currentItem = self.$pillGroup.find( '> .pill:nth-child(' + index + ')' );

						if ( $currentItem ) {
							$currentItem.remove();
						} else {
							break;
						}
					}
				}
			},

			// First parameter is index (optional)
			// Second parameter is new arguments
			placeItems: function placeItems() {
				var items;
				var index;
				var $neighbor;
				var isInternal;

				if ( isFinite( String( arguments[ 0 ] ) ) && !( arguments[ 0 ] instanceof Array ) ) {
					items = [].slice.call( arguments ).slice( 1 );
					index = arguments[ 0 ];
				} else {
					items = [].slice.call( arguments ).slice( 0 );
					isInternal = items[ 1 ] && !items[ 1 ].text;
				}

				if ( items[ 0 ] instanceof Array ) {
					items = items[ 0 ];
				}

				if ( items.length ) {
					var newItems = [];
					$.each( items, function prepareItemForAdd( i, item ) {
						var $item = $( item.el );

						$item.attr( 'data-value', item.value );
						$item.find( 'span:first' ).html( item.text );

						// DOM attributes
						if ( item.attr ) {
							$.each( item.attr, function handleDOMAttributes( key, value ) {
								if ( key === 'cssClass' || key === 'class' ) {
									$item.addClass( value );
								} else {
									$item.attr( key, value );
								}
							} );
						}

						if ( item.data ) {
							$item.data( 'data', item.data );
						}

						newItems.push( $item );
					} );

					if ( this.$pillGroup.children( '.pill' ).length > 0 ) {
						if ( index ) {
							$neighbor = this.$pillGroup.find( '.pill:nth-child(' + index + ')' );

							if ( $neighbor.length ) {
								$neighbor.before( newItems );
							} else {
								this.$pillGroup.children( '.pill:last' ).after( newItems );
							}
						} else {
							this.$pillGroup.children( '.pill:last' ).after( newItems );
						}
					} else {
						this.$pillGroup.prepend( newItems );
					}

					if ( isInternal ) {
						this.$element.trigger( 'added.fu.pillbox', {
							text: items[ 0 ].text,
							value: items[ 0 ].value
						} );
					}
				}
			},

			inputEvent: function inputEvent( e ) {
				var self = this;
				var text = self.options.cleanInput( this.$addItem.val() );
				var isFocusOutEvent = e.type === 'focusout';
				var blurredAfterInput = ( isFocusOutEvent && text.length > 0 );
				// If we test for keycode only, it will match for `<` & `,` instead of just `,`
				// This way users can type `<3` and `1 < 3`, etc...
				var acceptKeyPressed = ( this.acceptKeyCodes[ e.keyCode ] && !isShiftHeld( e ) );

				if ( acceptKeyPressed || blurredAfterInput ) {
					var attr;
					var value;

					if ( this.options.onKeyDown && this._isSuggestionsOpen() ) {
						var $selection = this.$suggest.find( '.pillbox-suggest-sel' );

						if ( $selection.length ) {
							text = self.options.cleanInput( $selection.html() );
							value = self.options.cleanInput( $selection.data( 'value' ) );
							attr = $selection.data( 'attr' );
						}
					}

					// ignore comma and make sure text that has been entered (protects against " ,". https://github.com/ExactTarget/fuelux/issues/593), unless allowEmptyPills is true.
					if ( text.replace( /[ ]*\,[ ]*/, '' ).match( /\S/ ) || ( this.options.allowEmptyPills && text.length ) ) {
						this._closeSuggestions();
						this.$addItem.val( '' ).hide();

						if ( attr ) {
							this.addItems( {
								text: text,
								value: value,
								attr: JSON.parse( attr )
							}, true );
						} else {
							this.addItems( {
								text: text,
								value: value
							}, true );
						}

						setTimeout( function clearAddItemInput() {
							self.$addItem.show().attr( {
								size: 10
							} ).focus();
						}, 0 );
					}

					e.preventDefault();
					return true;
				} else if ( isBackspaceKey( e ) || isDeleteKey( e ) ) {
					if ( !text.length ) {
						e.preventDefault();

						if ( this.options.edit && this.currentEdit ) {
							this.cancelEdit();
							return true;
						}

						this._closeSuggestions();
						var $lastItem = this.$pillGroup.children( '.pill:last' );

						if ( $lastItem.hasClass( 'pillbox-highlight' ) ) {
							this._removeElement( this.getItemData( $lastItem, {
								el: $lastItem
							} ) );
						} else {
							$lastItem.addClass( 'pillbox-highlight' );
						}

						return true;
					}
				} else if ( text.length > 10 ) {
					if ( this.$addItem.width() < ( this.$pillGroup.width() - 6 ) ) {
						this.$addItem.attr( {
							size: text.length + 3
						} );
					}
				}

				this.$pillGroup.find( '.pill' ).removeClass( 'pillbox-highlight' );

				if ( this.options.onKeyDown && !isFocusOutEvent ) {
					if (
						isTabKey( e ) ||
						isUpArrow( e ) ||
						isDownArrow( e )
					) {
						if ( this._isSuggestionsOpen() ) {
							this._keySuggestions( e );
						}

						return true;
					}

					// only allowing most recent event callback to register
					this.callbackId = e.timeStamp;
					this.options.onKeyDown( {
						event: e,
						value: text
					}, function callOpenSuggestions( data ) {
						self._openSuggestions( e, data );
					} );
				}

				return true;
			},

			openEdit: function openEdit( el ) {
				var targetChildIndex = el.index() + 1;
				var $addItemWrap = this.$addItemWrap.detach().hide();

				this.$pillGroup.find( '.pill:nth-child(' + targetChildIndex + ')' ).before( $addItemWrap );
				this.currentEdit = el.detach();

				$addItemWrap.addClass( 'editing' );
				this.$addItem.val( el.find( 'span:first' ).html() );
				$addItemWrap.show();
				this.$addItem.focus().select();
			},

			cancelEdit: function cancelEdit( e ) {
				var $addItemWrap;
				if ( !this.currentEdit ) {
					return false;
				}

				this._closeSuggestions();
				if ( e ) {
					this.$addItemWrap.before( this.currentEdit );
				}

				this.currentEdit = false;

				$addItemWrap = this.$addItemWrap.detach();
				$addItemWrap.removeClass( 'editing' );
				this.$addItem.val( '' );
				this.$pillGroup.append( $addItemWrap );

				return true;
			},

			// Must match syntax of placeItem so addItem callback is called when an item is edited
			// expecting to receive an array back from the callback containing edited items
			saveEdit: function saveEdit() {
				var item = arguments[ 0 ][ 0 ] ? arguments[ 0 ][ 0 ] : arguments[ 0 ];

				this.currentEdit = $( item.el );
				this.currentEdit.data( 'value', item.value );
				this.currentEdit.find( 'span:first' ).html( item.text );

				this.$addItemWrap.hide();
				this.$addItemWrap.before( this.currentEdit );
				this.currentEdit = false;

				this.$addItem.val( '' );
				this.$addItemWrap.removeClass( 'editing' );
				this.$pillGroup.append( this.$addItemWrap.detach().show() );
				this.$element.trigger( 'edited.fu.pillbox', {
					value: item.value,
					text: item.text
				} );
			},

			removeBySelector: function removeBySelector() {
				var selectors = [].slice.call( arguments ).slice( 0 );
				var self = this;

				$.each( selectors, function doRemove( i, sel ) {
					self.$pillGroup.find( sel ).remove();
				} );

				this._removePillTrigger( {
					method: 'removeBySelector',
					removedSelectors: selectors
				} );
			},

			removeByValue: function removeByValue() {
				var values = [].slice.call( arguments ).slice( 0 );
				var self = this;

				$.each( values, function doRemove( i, val ) {
					self.$pillGroup.find( '> .pill[data-value="' + val + '"]' ).remove();
				} );

				this._removePillTrigger( {
					method: 'removeByValue',
					removedValues: values
				} );
			},

			removeByText: function removeByText() {
				var text = [].slice.call( arguments ).slice( 0 );
				var self = this;

				$.each( text, function doRemove( i, matchingText ) {
					self.$pillGroup.find( '> .pill:contains("' + matchingText + '")' ).remove();
				} );

				this._removePillTrigger( {
					method: 'removeByText',
					removedText: text
				} );
			},

			truncate: function truncate( enable ) {
				var self = this;

				this.$element.removeClass( 'truncate' );
				this.$addItemWrap.removeClass( 'truncated' );
				this.$pillGroup.find( '.pill' ).removeClass( 'truncated' );

				if ( enable ) {
					this.$element.addClass( 'truncate' );

					var availableWidth = this.$element.width();
					var containerFull = false;
					var processedPills = 0;
					var totalPills = this.$pillGroup.find( '.pill' ).length;
					var widthUsed = 0;

					this.$pillGroup.find( '.pill' ).each( function processPills() {
						var pill = $( this );
						if ( !containerFull ) {
							processedPills++;
							self.$moreCount.text( totalPills - processedPills );
							if ( ( widthUsed + pill.outerWidth( true ) + self.$addItemWrap.outerWidth( true ) ) <= availableWidth ) {
								widthUsed += pill.outerWidth( true );
							} else {
								self.$moreCount.text( ( totalPills - processedPills ) + 1 );
								pill.addClass( 'truncated' );
								containerFull = true;
							}
						} else {
							pill.addClass( 'truncated' );
						}
					} );
					if ( processedPills === totalPills ) {
						this.$addItemWrap.addClass( 'truncated' );
					}
				}
			},

			inputFocus: function inputFocus() {
				this.$element.find( '.pillbox-add-item' ).focus();
			},

			getItemData: function getItemData( el, data ) {
				return $.extend( {
					text: el.find( 'span:first' ).html()
				}, el.data(), data );
			},

			_removeElement: function _removeElement( data ) {
				data.el.remove();
				delete data.el;
				this.$element.trigger( 'removed.fu.pillbox', data );
			},

			_removePillTrigger: function _removePillTrigger( removedBy ) {
				this.$element.trigger( 'removed.fu.pillbox', removedBy );
			},

			_generateObject: function _generateObject( data ) {
				var obj = {};

				$.each( data, function setObjectValue( index, value ) {
					obj[ value ] = true;
				} );

				return obj;
			},

			_openSuggestions: function _openSuggestions( e, data ) {
				var $suggestionList = $( '<ul>' );

				if ( this.callbackId !== e.timeStamp ) {
					return false;
				}

				if ( data.data && data.data.length ) {
					$.each( data.data, function appendSuggestions( index, value ) {
						var val = value.value ? value.value : value.text;

						// markup concatentation is 10x faster, but does not allow data store
						var $suggestion = $( '<li data-value="' + val + '">' + value.text + '</li>' );

						if ( value.attr ) {
							$suggestion.data( 'attr', JSON.stringify( value.attr ) );
						}

						if ( value.data ) {
							$suggestion.data( 'data', value.data );
						}

						$suggestionList.append( $suggestion );
					} );

					// suggestion dropdown
					this.$suggest.html( '' ).append( $suggestionList.children() );
					$( document ).trigger( 'suggested.fu.pillbox', this.$suggest );
				}

				return true;
			},

			_closeSuggestions: function _closeSuggestions() {
				this.$suggest.html( '' ).parent().removeClass( 'open' );
			},

			_isSuggestionsOpen: function _isSuggestionsOpen() {
				return this.$suggest.parent().hasClass( 'open' );
			},

			_keySuggestions: function _keySuggestions( e ) {
				var $first = this.$suggest.find( 'li.pillbox-suggest-sel' );
				var dir = isUpArrow( e );

				e.preventDefault();

				if ( !$first.length ) {
					$first = this.$suggest.find( 'li:first' );
					$first.addClass( 'pillbox-suggest-sel' );
				} else {
					var $next = dir ? $first.prev() : $first.next();

					if ( !$next.length ) {
						$next = dir ? this.$suggest.find( 'li:last' ) : this.$suggest.find( 'li:first' );
					}

					if ( $next ) {
						$next.addClass( 'pillbox-suggest-sel' );
						$first.removeClass( 'pillbox-suggest-sel' );
					}
				}
			}
		};

		Pillbox.prototype.getValue = Pillbox.prototype.items;

		// PILLBOX PLUGIN DEFINITION

		$.fn.pillbox = function pillbox( option ) {
			var args = Array.prototype.slice.call( arguments, 1 );
			var methodReturn;

			var $set = this.each( function set() {
				var $this = $( this );
				var data = $this.data( 'fu.pillbox' );
				var options = typeof option === 'object' && option;

				if ( !data ) {
					$this.data( 'fu.pillbox', ( data = new Pillbox( this, options ) ) );
				}

				if ( typeof option === 'string' ) {
					methodReturn = data[ option ].apply( data, args );
				}
			} );

			return ( methodReturn === undefined ) ? $set : methodReturn;
		};

		$.fn.pillbox.defaults = {
			edit: false,
			readonly: -1, // can be true or false. -1 means it will check for data-readonly="readonly"
			truncate: false,
			acceptKeyCodes: [
				ENTER_KEYCODE,
				COMMA_KEYCODE
			],
			allowEmptyPills: false,
			cleanInput: cleanInput

			// example on remove
			/* onRemove: function(data,callback){
				console.log('onRemove');
				callback(data);
			} */

			// example on key down
			/* onKeyDown: function(event, data, callback ){
				callback({data:[
					{text: Math.random(),value:'sdfsdfsdf'},
					{text: Math.random(),value:'sdfsdfsdf'}
				]});
			}
			*/
			// example onAdd
			/* onAdd: function( data, callback ){
				console.log(data, callback);
				callback(data);
			} */
		};

		$.fn.pillbox.Constructor = Pillbox;

		$.fn.pillbox.noConflict = function noConflict() {
			$.fn.pillbox = old;
			return this;
		};


		// DATA-API

		$( document ).on( 'mousedown.fu.pillbox.data-api', '[data-initialize=pillbox]', function dataAPI( e ) {
			var $control = $( e.target ).closest( '.pillbox' );
			if ( !$control.data( 'fu.pillbox' ) ) {
				$control.pillbox( $control.data() );
			}
		} );

		// Must be domReady for AMD compatibility
		$( function DOMReady() {
			$( '[data-initialize=pillbox]' ).each( function init() {
				var $this = $( this );
				if ( $this.data( 'fu.pillbox' ) ) return;
				$this.pillbox( $this.data() );
			} );
		} );



	} )( jQuery );


	( function( $ ) {

		/* global jQuery:true */

		/*
		 * Fuel UX Repeater
		 * https://github.com/ExactTarget/fuelux
		 *
		 * Copyright (c) 2014 ExactTarget
		 * Licensed under the BSD New license.
		 */



		// -- BEGIN MODULE CODE HERE --

		var old = $.fn.repeater;

		// REPEATER CONSTRUCTOR AND PROTOTYPE

		var Repeater = function Repeater( element, options ) {
			var self = this;
			var $btn;
			var currentView;

			this.$element = $( element );

			this.$canvas = this.$element.find( '.repeater-canvas' );
			this.$count = this.$element.find( '.repeater-count' );
			this.$end = this.$element.find( '.repeater-end' );
			this.$filters = this.$element.find( '.repeater-filters' );
			this.$loader = this.$element.find( '.repeater-loader' );
			this.$pageSize = this.$element.find( '.repeater-itemization .selectlist' );
			this.$nextBtn = this.$element.find( '.repeater-next' );
			this.$pages = this.$element.find( '.repeater-pages' );
			this.$prevBtn = this.$element.find( '.repeater-prev' );
			this.$primaryPaging = this.$element.find( '.repeater-primaryPaging' );
			this.$search = this.$element.find( '.repeater-search' ).find( '.search' );
			this.$secondaryPaging = this.$element.find( '.repeater-secondaryPaging' );
			this.$start = this.$element.find( '.repeater-start' );
			this.$viewport = this.$element.find( '.repeater-viewport' );
			this.$views = this.$element.find( '.repeater-views' );

			this.currentPage = 0;
			this.currentView = null;
			this.isDisabled = false;
			this.infiniteScrollingCallback = function noop() {};
			this.infiniteScrollingCont = null;
			this.infiniteScrollingEnabled = false;
			this.infiniteScrollingEnd = null;
			this.infiniteScrollingOptions = {};
			this.lastPageInput = 0;
			this.options = $.extend( {}, $.fn.repeater.defaults, options );
			this.pageIncrement = 0; // store direction navigated
			this.resizeTimeout = {};
			this.stamp = new Date().getTime() + ( Math.floor( Math.random() * 100 ) + 1 );
			this.storedDataSourceOpts = null;
			this.syncingViewButtonState = false;
			this.viewOptions = {};
			this.viewType = null;

			this.$filters.selectlist();
			this.$pageSize.selectlist();
			this.$primaryPaging.find( '.combobox' ).combobox();
			this.$search.search( {
				searchOnKeyPress: this.options.searchOnKeyPress,
				allowCancel: this.options.allowCancel
			} );

			this.$filters.on( 'changed.fu.selectlist', function onFiltersChanged( e, value ) {
				self.$element.trigger( 'filtered.fu.repeater', value );
				self.render( {
					clearInfinite: true,
					pageIncrement: null
				} );
			} );
			this.$nextBtn.on( 'click.fu.repeater', $.proxy( this.next, this ) );
			this.$pageSize.on( 'changed.fu.selectlist', function onPageSizeChanged( e, value ) {
				self.$element.trigger( 'pageSizeChanged.fu.repeater', value );
				self.render( {
					pageIncrement: null
				} );
			} );
			this.$prevBtn.on( 'click.fu.repeater', $.proxy( this.previous, this ) );
			this.$primaryPaging.find( '.combobox' ).on( 'changed.fu.combobox', function onPrimaryPagingChanged( evt, data ) {
				self.pageInputChange( data.text, data );
			} );
			this.$search.on( 'searched.fu.search cleared.fu.search', function onSearched( e, value ) {
				self.$element.trigger( 'searchChanged.fu.repeater', value );
				self.render( {
					clearInfinite: true,
					pageIncrement: null
				} );
			} );
			this.$search.on( 'canceled.fu.search', function onSearchCanceled( e, value ) {
				self.$element.trigger( 'canceled.fu.repeater', value );
				self.render( {
					clearInfinite: true,
					pageIncrement: null
				} );
			} );

			this.$secondaryPaging.on( 'blur.fu.repeater', function onSecondaryPagingBlur() {
				self.pageInputChange( self.$secondaryPaging.val() );
			} );
			this.$secondaryPaging.on( 'keyup', function onSecondaryPagingKeyup( e ) {
				if ( e.keyCode === 13 ) {
					self.pageInputChange( self.$secondaryPaging.val() );
				}
			} );
			this.$views.find( 'input' ).on( 'change.fu.repeater', $.proxy( this.viewChanged, this ) );

			$( window ).on( 'resize.fu.repeater.' + this.stamp, function onResizeRepeater() {
				clearTimeout( self.resizeTimeout );
				self.resizeTimeout = setTimeout( function resizeTimeout() {
					self.resize();
					self.$element.trigger( 'resized.fu.repeater' );
				}, 75 );
			} );

			this.$loader.loader();
			this.$loader.loader( 'pause' );
			if ( this.options.defaultView !== -1 ) {
				currentView = this.options.defaultView;
			} else {
				$btn = this.$views.find( 'label.active input' );
				currentView = ( $btn.length > 0 ) ? $btn.val() : 'list';
			}

			this.setViewOptions( currentView );

			this.initViewTypes( function initViewTypes() {
				self.resize();
				self.$element.trigger( 'resized.fu.repeater' );
				self.render( {
					changeView: currentView
				} );
			} );
		};

		var logWarn = function logWarn( msg ) {
			if ( window.console && window.console.warn ) {
				window.console.warn( msg );
			}
		};

		var scan = function scan( cont ) {
			var keep = [];
			cont.children().each( function eachContainerChild() {
				var item = $( this );
				var pres = item.attr( 'data-preserve' );
				if ( pres === 'deep' ) {
					item.detach();
					keep.push( item );
				} else if ( pres === 'shallow' ) {
					scan( item );
					item.detach();
					keep.push( item );
				}
			} );
			cont.empty();
			cont.append( keep );
		};

		var addItem = function addItem( $parent, response ) {
			var action;
			if ( response ) {
				action = ( response.action ) ? response.action : 'append';
				if ( action !== 'none' && response.item !== undefined ) {
					var $container = ( response.container !== undefined ) ? $( response.container ) : $parent;
					$container[ action ]( response.item );
				}
			}
		};

		var callNextInit = function callNextInit( currentViewType, viewTypes, callback ) {
			var nextViewType = currentViewType + 1;
			if ( nextViewType < viewTypes.length ) {
				initViewType.call( this, nextViewType, viewTypes, callback );
			} else {
				callback();
			}
		};

		var initViewType = function initViewType( currentViewtype, viewTypes, callback ) {
			if ( viewTypes[ currentViewtype ].initialize ) {
				viewTypes[ currentViewtype ].initialize.call( this, {}, function afterInitialize() {
					callNextInit.call( this, currentViewtype, viewTypes, callback );
				} );
			} else {
				callNextInit.call( this, currentViewtype, viewTypes, callback );
			}
		};

		// Does all of our cleanup post-render
		var afterRender = function afterRender( state ) {
			var data = state.data || {};

			if ( this.infiniteScrollingEnabled ) {
				if ( state.viewChanged || state.options.clearInfinite ) {
					this.initInfiniteScrolling();
				}

				this.infiniteScrollPaging( data, state.options );
			}

			this.$loader.hide().loader( 'pause' );
			this.enable();

			this.$search.trigger( 'rendered.fu.repeater', {
				data: data,
				options: state.dataOptions,
				renderOptions: state.options
			} );
			this.$element.trigger( 'rendered.fu.repeater', {
				data: data,
				options: state.dataOptions,
				renderOptions: state.options
			} );

			// for maintaining support of 'loaded' event
			this.$element.trigger( 'loaded.fu.repeater', state.dataOptions );
		};

		// This does the actual rendering of the repeater
		var doRender = function doRender( state ) {
			var data = state.data || {};

			if ( this.infiniteScrollingEnabled ) {
				// pass empty object because data handled in infiniteScrollPaging method
				this.infiniteScrollingCallback( {} );
			} else {
				this.itemization( data );
				this.pagination( data );
			}

			var self = this;
			this.renderItems(
				state.viewTypeObj,
				data,
				function callAfterRender( d ) {
					state.data = d;
					afterRender.call( self, state );
				}
			);
		};

		Repeater.prototype = {
			constructor: Repeater,

			clear: function clear( opts ) {
				var options = opts || {};

				if ( !options.preserve ) {
					// Just trash everything because preserve is false
					this.$canvas.empty();
				} else if ( !this.infiniteScrollingEnabled || options.clearInfinite ) {
					// Preserve clear only if infiniteScrolling is disabled or if specifically told to do so
					scan( this.$canvas );
				} // Otherwise don't clear because infiniteScrolling is enabled

				// If viewChanged and current viewTypeObj has a cleared function, call it
				var viewChanged = ( options.viewChanged !== undefined ) ? options.viewChanged : false;
				var viewTypeObj = $.fn.repeater.viewTypes[ this.viewType ] || {};
				if ( !viewChanged && viewTypeObj.cleared ) {
					viewTypeObj.cleared.call( this, {
						options: options
					} );
				}
			},

			clearPreservedDataSourceOptions: function clearPreservedDataSourceOptions() {
				this.storedDataSourceOpts = null;
			},

			destroy: function destroy() {
				var markup;
				// set input value attrbute in markup
				this.$element.find( 'input' ).each( function eachInput() {
					$( this ).attr( 'value', $( this ).val() );
				} );

				// empty elements to return to original markup
				this.$canvas.empty();
				markup = this.$element[ 0 ].outerHTML;

				// destroy components and remove leftover
				this.$element.find( '.combobox' ).combobox( 'destroy' );
				this.$element.find( '.selectlist' ).selectlist( 'destroy' );
				this.$element.find( '.search' ).search( 'destroy' );
				if ( this.infiniteScrollingEnabled ) {
					$( this.infiniteScrollingCont ).infinitescroll( 'destroy' );
				}

				this.$element.remove();

				// any external events
				$( window ).off( 'resize.fu.repeater.' + this.stamp );

				return markup;
			},

			disable: function disable() {
				var viewTypeObj = $.fn.repeater.viewTypes[ this.viewType ] || {};

				this.$search.search( 'disable' );
				this.$filters.selectlist( 'disable' );
				this.$views.find( 'label, input' ).addClass( 'disabled' ).attr( 'disabled', 'disabled' );
				this.$pageSize.selectlist( 'disable' );
				this.$primaryPaging.find( '.combobox' ).combobox( 'disable' );
				this.$secondaryPaging.attr( 'disabled', 'disabled' );
				this.$prevBtn.attr( 'disabled', 'disabled' );
				this.$nextBtn.attr( 'disabled', 'disabled' );

				if ( viewTypeObj.enabled ) {
					viewTypeObj.enabled.call( this, {
						status: false
					} );
				}

				this.isDisabled = true;
				this.$element.addClass( 'disabled' );
				this.$element.trigger( 'disabled.fu.repeater' );
			},

			enable: function enable() {
				var viewTypeObj = $.fn.repeater.viewTypes[ this.viewType ] || {};

				this.$search.search( 'enable' );
				this.$filters.selectlist( 'enable' );
				this.$views.find( 'label, input' ).removeClass( 'disabled' ).removeAttr( 'disabled' );
				this.$pageSize.selectlist( 'enable' );
				this.$primaryPaging.find( '.combobox' ).combobox( 'enable' );
				this.$secondaryPaging.removeAttr( 'disabled' );

				if ( !this.$prevBtn.hasClass( 'page-end' ) ) {
					this.$prevBtn.removeAttr( 'disabled' );
				}
				if ( !this.$nextBtn.hasClass( 'page-end' ) ) {
					this.$nextBtn.removeAttr( 'disabled' );
				}

				// is 0 or 1 pages, if using $primaryPaging (combobox)
				// if using selectlist allow user to use selectlist to select 0 or 1
				if ( this.$prevBtn.hasClass( 'page-end' ) && this.$nextBtn.hasClass( 'page-end' ) ) {
					this.$primaryPaging.combobox( 'disable' );
				}

				// if there are no items
				if ( parseInt( this.$count.html(), 10 ) !== 0 ) {
					this.$pageSize.selectlist( 'enable' );
				} else {
					this.$pageSize.selectlist( 'disable' );
				}

				if ( viewTypeObj.enabled ) {
					viewTypeObj.enabled.call( this, {
						status: true
					} );
				}

				this.isDisabled = false;
				this.$element.removeClass( 'disabled' );
				this.$element.trigger( 'enabled.fu.repeater' );
			},

			getDataOptions: function getDataOptions( opts ) {
				var options = opts || {};
				if ( options.pageIncrement !== undefined ) {
					if ( options.pageIncrement === null ) {
						this.currentPage = 0;
					} else {
						this.currentPage += options.pageIncrement;
					}
				}

				var dataSourceOptions = {};
				if ( options.dataSourceOptions ) {
					dataSourceOptions = options.dataSourceOptions;

					if ( options.preserveDataSourceOptions ) {
						if ( this.storedDataSourceOpts ) {
							this.storedDataSourceOpts = $.extend( this.storedDataSourceOpts, dataSourceOptions );
						} else {
							this.storedDataSourceOpts = dataSourceOptions;
						}
					}
				}

				if ( this.storedDataSourceOpts ) {
					dataSourceOptions = $.extend( this.storedDataSourceOpts, dataSourceOptions );
				}

				var returnOptions = {
					view: this.currentView,
					pageIndex: this.currentPage,
					filter: {
						text: 'All',
						value: 'all'
					}
				};
				if ( this.$filters.length > 0 ) {
					returnOptions.filter = this.$filters.selectlist( 'selectedItem' );
				}

				if ( !this.infiniteScrollingEnabled ) {
					returnOptions.pageSize = 25;

					if ( this.$pageSize.length > 0 ) {
						returnOptions.pageSize = parseInt( this.$pageSize.selectlist( 'selectedItem' ).value, 10 );
					}
				}

				var searchValue = this.$search && this.$search.find( 'input' ) && this.$search.find( 'input' ).val();
				if ( searchValue !== '' ) {
					returnOptions.search = searchValue;
				}

				var viewType = $.fn.repeater.viewTypes[ this.viewType ] || {};
				var addViewTypeData = viewType.dataOptions;
				if ( addViewTypeData ) {
					returnOptions = addViewTypeData.call( this, returnOptions );
				}

				returnOptions = $.extend( returnOptions, dataSourceOptions );

				return returnOptions;
			},

			infiniteScrolling: function infiniteScrolling( enable, opts ) {
				var footer = this.$element.find( '.repeater-footer' );
				var viewport = this.$element.find( '.repeater-viewport' );
				var options = opts || {};

				if ( enable ) {
					this.infiniteScrollingEnabled = true;
					this.infiniteScrollingEnd = options.end;
					delete options.dataSource;
					delete options.end;
					this.infiniteScrollingOptions = options;
					viewport.css( {
						height: viewport.height() + footer.outerHeight()
					} );
					footer.hide();
				} else {
					var cont = this.infiniteScrollingCont;
					var data = cont.data();
					delete data.infinitescroll;
					cont.off( 'scroll' );
					cont.removeClass( 'infinitescroll' );

					this.infiniteScrollingCont = null;
					this.infiniteScrollingEnabled = false;
					this.infiniteScrollingEnd = null;
					this.infiniteScrollingOptions = {};
					viewport.css( {
						height: viewport.height() - footer.outerHeight()
					} );
					footer.show();
				}
			},

			infiniteScrollPaging: function infiniteScrollPaging( data ) {
				var end = ( this.infiniteScrollingEnd !== true ) ? this.infiniteScrollingEnd : undefined;
				var page = data.page;
				var pages = data.pages;

				this.currentPage = ( page !== undefined ) ? page : NaN;

				if ( this.infiniteScrollingCont ) {
					if ( data.end === true || ( this.currentPage + 1 ) >= pages ) {
						this.infiniteScrollingCont.infinitescroll( 'end', end );
					} else {
						this.infiniteScrollingCont.infinitescroll( 'onScroll' );
					}
				}
			},

			initInfiniteScrolling: function initInfiniteScrolling() {
				var cont = this.$canvas.find( '[data-infinite="true"]:first' );

				cont = ( cont.length < 1 ) ? this.$canvas : cont;
				if ( cont.data( 'fu.infinitescroll' ) ) {
					cont.infinitescroll( 'enable' );
				} else {
					var self = this;
					var opts = $.extend( {}, this.infiniteScrollingOptions );
					opts.dataSource = function dataSource( helpers, callback ) {
						self.infiniteScrollingCallback = callback;
						self.render( {
							pageIncrement: 1
						} );
					};
					cont.infinitescroll( opts );
					this.infiniteScrollingCont = cont;
				}
			},

			initViewTypes: function initViewTypes( callback ) {
				var viewTypes = [];

				for ( var key in $.fn.repeater.viewTypes ) {
					if ( {}.hasOwnProperty.call( $.fn.repeater.viewTypes, key ) ) {
						viewTypes.push( $.fn.repeater.viewTypes[ key ] );
					}
				}

				if ( viewTypes.length > 0 ) {
					initViewType.call( this, 0, viewTypes, callback );
				} else {
					callback();
				}
			},

			itemization: function itemization( data ) {
				this.$count.html( ( data.count !== undefined ) ? data.count : '?' );
				this.$end.html( ( data.end !== undefined ) ? data.end : '?' );
				this.$start.html( ( data.start !== undefined ) ? data.start : '?' );
			},

			next: function next() {
				this.$nextBtn.attr( 'disabled', 'disabled' );
				this.$prevBtn.attr( 'disabled', 'disabled' );
				this.pageIncrement = 1;
				this.$element.trigger( 'nextClicked.fu.repeater' );
				this.render( {
					pageIncrement: this.pageIncrement
				} );
			},

			pageInputChange: function pageInputChange( val, dataFromCombobox ) {
				// dataFromCombobox is a proxy for data from combobox's changed event,
				// if no combobox is present data will be undefined
				var pageInc;
				if ( val !== this.lastPageInput ) {
					this.lastPageInput = val;
					var value = parseInt( val, 10 ) - 1;
					pageInc = value - this.currentPage;
					this.$element.trigger( 'pageChanged.fu.repeater', [ value, dataFromCombobox ] );
					this.render( {
						pageIncrement: pageInc
					} );
				}
			},

			pagination: function pagination( data ) {
				this.$primaryPaging.removeClass( 'active' );
				this.$secondaryPaging.removeClass( 'active' );

				var totalPages = data.pages;
				this.currentPage = ( data.page !== undefined ) ? data.page : NaN;
				// set paging to 0 if total pages is 0, otherwise use one-based index
				var currenPageOutput = totalPages === 0 ? 0 : this.currentPage + 1;

				if ( totalPages <= this.viewOptions.dropPagingCap ) {
					this.$primaryPaging.addClass( 'active' );
					var dropMenu = this.$primaryPaging.find( '.dropdown-menu' );
					dropMenu.empty();
					for ( var i = 0; i < totalPages; i++ ) {
						var l = i + 1;
						dropMenu.append( '<li data-value="' + l + '"><a href="#">' + l + '</a></li>' );
					}

					this.$primaryPaging.find( 'input.form-control' ).val( currenPageOutput );
				} else {
					this.$secondaryPaging.addClass( 'active' );
					this.$secondaryPaging.val( currenPageOutput );
				}

				this.lastPageInput = this.currentPage + 1 + '';

				this.$pages.html( '' + totalPages );

				// this is not the last page
				if ( ( this.currentPage + 1 ) < totalPages ) {
					this.$nextBtn.removeAttr( 'disabled' );
					this.$nextBtn.removeClass( 'page-end' );
				} else {
					this.$nextBtn.attr( 'disabled', 'disabled' );
					this.$nextBtn.addClass( 'page-end' );
				}

				// this is not the first page
				if ( ( this.currentPage - 1 ) >= 0 ) {
					this.$prevBtn.removeAttr( 'disabled' );
					this.$prevBtn.removeClass( 'page-end' );
				} else {
					this.$prevBtn.attr( 'disabled', 'disabled' );
					this.$prevBtn.addClass( 'page-end' );
				}

				// return focus to next/previous buttons after navigating
				if ( this.pageIncrement !== 0 ) {
					if ( this.pageIncrement > 0 ) {
						if ( this.$nextBtn.is( ':disabled' ) ) {
							// if you can't focus, go the other way
							this.$prevBtn.focus();
						} else {
							this.$nextBtn.focus();
						}
					} else if ( this.$prevBtn.is( ':disabled' ) ) {
						// if you can't focus, go the other way
						this.$nextBtn.focus();
					} else {
						this.$prevBtn.focus();
					}
				}
			},

			previous: function previous() {
				this.$nextBtn.attr( 'disabled', 'disabled' );
				this.$prevBtn.attr( 'disabled', 'disabled' );
				this.pageIncrement = -1;
				this.$element.trigger( 'previousClicked.fu.repeater' );
				this.render( {
					pageIncrement: this.pageIncrement
				} );
			},

			// This functions more as a "pre-render" than a true "render"
			render: function render( opts ) {
				this.disable();

				var viewChanged = false;
				var viewTypeObj = $.fn.repeater.viewTypes[ this.viewType ] || {};
				var options = opts || {};

				if ( options.changeView && ( this.currentView !== options.changeView ) ) {
					var prevView = this.currentView;
					this.currentView = options.changeView;
					this.viewType = this.currentView.split( '.' )[ 0 ];
					this.setViewOptions( this.currentView );
					this.$element.attr( 'data-currentview', this.currentView );
					this.$element.attr( 'data-viewtype', this.viewType );
					viewChanged = true;
					options.viewChanged = viewChanged;

					this.$element.trigger( 'viewChanged.fu.repeater', this.currentView );

					if ( this.infiniteScrollingEnabled ) {
						this.infiniteScrolling( false );
					}

					viewTypeObj = $.fn.repeater.viewTypes[ this.viewType ] || {};
					if ( viewTypeObj.selected ) {
						viewTypeObj.selected.call( this, {
							prevView: prevView
						} );
					}
				}

				this.syncViewButtonState();

				options.preserve = ( options.preserve !== undefined ) ? options.preserve : !viewChanged;
				this.clear( options );

				if ( !this.infiniteScrollingEnabled || ( this.infiniteScrollingEnabled && viewChanged ) ) {
					this.$loader.show().loader( 'play' );
				}

				var dataOptions = this.getDataOptions( options );

				var beforeRender = this.viewOptions.dataSource;
				var repeaterPrototypeContext = this;
				beforeRender(
					dataOptions,
					// this serves as a bridge function to pass all required data through to the actual function
					// that does the rendering for us.
					function callDoRender( dataSourceReturnedData ) {
						doRender.call(
							repeaterPrototypeContext, {
								data: dataSourceReturnedData,
								dataOptions: dataOptions,
								options: options,
								viewChanged: viewChanged,
								viewTypeObj: viewTypeObj
							}
						);
					}
				);
			},

			resize: function resize() {
				var staticHeight = ( this.viewOptions.staticHeight === -1 ) ? this.$element.attr( 'data-staticheight' ) : this.viewOptions.staticHeight;
				var viewTypeObj = {};
				var height;
				var viewportMargins;
				var scrubbedElements = [];
				var previousProperties = [];
				var $hiddenElements = this.$element.parentsUntil( ':visible' ).addBack();
				var currentHiddenElement;
				var currentElementIndex = 0;

				// Set parents to 'display:block' until repeater is visible again
				while ( currentElementIndex < $hiddenElements.length && this.$element.is( ':hidden' ) ) {
					currentHiddenElement = $hiddenElements[ currentElementIndex ];
					// Only set display property on elements that are explicitly hidden (i.e. do not inherit it from their parent)
					if ( $( currentHiddenElement ).is( ':hidden' ) ) {
						previousProperties.push( currentHiddenElement.style[ 'display' ] );
						currentHiddenElement.style[ 'display' ] = 'block';
						scrubbedElements.push( currentHiddenElement );
					}
					currentElementIndex++;
				}

				if ( this.viewType ) {
					viewTypeObj = $.fn.repeater.viewTypes[ this.viewType ] || {};
				}

				if ( staticHeight !== undefined && staticHeight !== false && staticHeight !== 'false' ) {
					this.$canvas.addClass( 'scrolling' );
					viewportMargins = {
						bottom: this.$viewport.css( 'margin-bottom' ),
						top: this.$viewport.css( 'margin-top' )
					};

					var staticHeightValue = ( staticHeight === 'true' || staticHeight === true ) ? this.$element.height() : parseInt( staticHeight, 10 );
					var headerHeight = this.$element.find( '.repeater-header' ).outerHeight() || 0;
					var footerHeight = this.$element.find( '.repeater-footer' ).outerHeight() || 0;
					var bottomMargin = ( viewportMargins.bottom === 'auto' ) ? 0 : parseInt( viewportMargins.bottom, 10 );
					var topMargin = ( viewportMargins.top === 'auto' ) ? 0 : parseInt( viewportMargins.top, 10 );

					height = staticHeightValue - headerHeight - footerHeight - bottomMargin - topMargin;
					this.$viewport.outerHeight( height );
				} else {
					this.$canvas.removeClass( 'scrolling' );
				}

				if ( viewTypeObj.resize ) {
					viewTypeObj.resize.call( this, {
						height: this.$element.outerHeight(),
						width: this.$element.outerWidth()
					} );
				}

				scrubbedElements.forEach( function( element, i ) {
					element.style[ 'display' ] = previousProperties[ i ];
				} );
			},

			// e.g. "Rows" or "Thumbnails"
			renderItems: function renderItems( viewTypeObj, data, callback ) {
				if ( !viewTypeObj.render ) {
					if ( viewTypeObj.before ) {
						var addBefore = viewTypeObj.before.call( this, {
							container: this.$canvas,
							data: data
						} );
						addItem( this.$canvas, addBefore );
					}

					var $dataContainer = this.$canvas.find( '[data-container="true"]:last' );
					var $container = ( $dataContainer.length > 0 ) ? $dataContainer : this.$canvas;

					// It appears that the following code would theoretically allow you to pass a deeply
					// nested value to "repeat on" to be added to the repeater.
					// eg. `data.foo.bar.items`
					if ( viewTypeObj.renderItem ) {
						var subset;
						var objectAndPropsToRepeatOnString = viewTypeObj.repeat || 'data.items';
						var objectAndPropsToRepeatOn = objectAndPropsToRepeatOnString.split( '.' );
						var objectToRepeatOn = objectAndPropsToRepeatOn[ 0 ];

						if ( objectToRepeatOn === 'data' || objectToRepeatOn === 'this' ) {
							subset = ( objectToRepeatOn === 'this' ) ? this : data;

							// Extracts subset from object chain (get `items` out of `foo.bar.items`). I think....
							var propsToRepeatOn = objectAndPropsToRepeatOn.slice( 1 );
							for ( var prop = 0; prop < propsToRepeatOn.length; prop++ ) {
								if ( subset[ propsToRepeatOn[ prop ] ] !== undefined ) {
									subset = subset[ propsToRepeatOn[ prop ] ];
								} else {
									subset = [];
									logWarn( 'WARNING: Repeater unable to find property to iterate renderItem on.' );
									break;
								}
							}

							for ( var subItemIndex = 0; subItemIndex < subset.length; subItemIndex++ ) {
								var addSubItem = viewTypeObj.renderItem.call( this, {
									container: $container,
									data: data,
									index: subItemIndex,
									subset: subset
								} );
								addItem( $container, addSubItem );
							}
						} else {
							logWarn( 'WARNING: Repeater plugin "repeat" value must start with either "data" or "this"' );
						}
					}

					if ( viewTypeObj.after ) {
						var addAfter = viewTypeObj.after.call( this, {
							container: this.$canvas,
							data: data
						} );
						addItem( this.$canvas, addAfter );
					}

					callback( data );
				} else {
					viewTypeObj.render.call( this, {
						container: this.$canvas,
						data: data
					}, callback );
				}
			},

			setViewOptions: function setViewOptions( curView ) {
				var opts = {};
				var viewName = curView.split( '.' )[ 1 ];

				if ( this.options.views ) {
					opts = this.options.views[ viewName ] || this.options.views[ curView ] || {};
				} else {
					opts = {};
				}

				this.viewOptions = $.extend( {}, this.options, opts );
			},

			viewChanged: function viewChanged( e ) {
				var $selected = $( e.target );
				var val = $selected.val();

				if ( !this.syncingViewButtonState ) {
					if ( this.isDisabled || $selected.parents( 'label:first' ).hasClass( 'disabled' ) ) {
						this.syncViewButtonState();
					} else {
						this.render( {
							changeView: val,
							pageIncrement: null
						} );
					}
				}
			},

			syncViewButtonState: function syncViewButtonState() {
				var $itemToCheck = this.$views.find( 'input[value="' + this.currentView + '"]' );

				this.syncingViewButtonState = true;
				this.$views.find( 'input' ).prop( 'checked', false );
				this.$views.find( 'label.active' ).removeClass( 'active' );

				if ( $itemToCheck.length > 0 ) {
					$itemToCheck.prop( 'checked', true );
					$itemToCheck.parents( 'label:first' ).addClass( 'active' );
				}
				this.syncingViewButtonState = false;
			}
		};

		// For backwards compatibility.
		Repeater.prototype.runRenderer = Repeater.prototype.renderItems;

		// REPEATER PLUGIN DEFINITION

		$.fn.repeater = function fnrepeater( option ) {
			var args = Array.prototype.slice.call( arguments, 1 );
			var methodReturn;

			var $set = this.each( function eachThis() {
				var $this = $( this );
				var data = $this.data( 'fu.repeater' );
				var options = typeof option === 'object' && option;

				if ( !data ) {
					$this.data( 'fu.repeater', ( data = new Repeater( this, options ) ) );
				}

				if ( typeof option === 'string' ) {
					methodReturn = data[ option ].apply( data, args );
				}
			} );

			return ( methodReturn === undefined ) ? $set : methodReturn;
		};

		$.fn.repeater.defaults = {
			dataSource: function dataSource( options, callback ) {
				callback( {
					count: 0,
					end: 0,
					items: [],
					page: 0,
					pages: 1,
					start: 0
				} );
			},
			defaultView: -1, // should be a string value. -1 means it will grab the active view from the view controls
			dropPagingCap: 10,
			staticHeight: -1, // normally true or false. -1 means it will look for data-staticheight on the element
			views: null, // can be set to an object to configure multiple views of the same type,
			searchOnKeyPress: false,
			allowCancel: true
		};

		$.fn.repeater.viewTypes = {};

		$.fn.repeater.Constructor = Repeater;

		$.fn.repeater.noConflict = function noConflict() {
			$.fn.repeater = old;
			return this;
		};



	} )( jQuery );


	( function( $ ) {

		/* global jQuery:true */

		/*
		 * Fuel UX Repeater - List View Plugin
		 * https://github.com/ExactTarget/fuelux
		 *
		 * Copyright (c) 2014 ExactTarget
		 * Licensed under the BSD New license.
		 */



		// -- BEGIN MODULE CODE HERE --

		if ( $.fn.repeater ) {
			// ADDITIONAL METHODS
			$.fn.repeater.Constructor.prototype.list_clearSelectedItems = function listClearSelectedItems() {
				this.$canvas.find( '.repeater-list-check' ).remove();
				this.$canvas.find( '.repeater-list table tbody tr.selected' ).removeClass( 'selected' );
			};

			$.fn.repeater.Constructor.prototype.list_highlightColumn = function listHighlightColumn( index, force ) {
				var tbody = this.$canvas.find( '.repeater-list-wrapper > table tbody' );
				if ( this.viewOptions.list_highlightSortedColumn || force ) {
					tbody.find( 'td.sorted' ).removeClass( 'sorted' );
					tbody.find( 'tr' ).each( function eachTR() {
						var col = $( this ).find( 'td:nth-child(' + ( index + 1 ) + ')' ).filter( function filterChildren() {
							return !$( this ).parent().hasClass( 'empty' );
						} );
						col.addClass( 'sorted' );
					} );
				}
			};

			$.fn.repeater.Constructor.prototype.list_getSelectedItems = function listGetSelectedItems() {
				var selected = [];
				this.$canvas.find( '.repeater-list .repeater-list-wrapper > table tbody tr.selected' ).each( function eachSelectedTR() {
					var $item = $( this );
					selected.push( {
						data: $item.data( 'item_data' ),
						element: $item
					} );
				} );
				return selected;
			};

			$.fn.repeater.Constructor.prototype.getValue = $.fn.repeater.Constructor.prototype.list_getSelectedItems;

			$.fn.repeater.Constructor.prototype.list_positionHeadings = function listPositionHeadings() {
				var $wrapper = this.$element.find( '.repeater-list-wrapper' );
				var offsetLeft = $wrapper.offset().left;
				var scrollLeft = $wrapper.scrollLeft();
				if ( scrollLeft > 0 ) {
					$wrapper.find( '.repeater-list-heading' ).each( function eachListHeading() {
						var $heading = $( this );
						var left = ( $heading.parents( 'th:first' ).offset().left - offsetLeft ) + 'px';
						$heading.addClass( 'shifted' ).css( 'left', left );
					} );
				} else {
					$wrapper.find( '.repeater-list-heading' ).each( function eachListHeading() {
						$( this ).removeClass( 'shifted' ).css( 'left', '' );
					} );
				}
			};

			$.fn.repeater.Constructor.prototype.list_setSelectedItems = function listSetSelectedItems( itms, force ) {
				var selectable = this.viewOptions.list_selectable;
				var self = this;
				var data;
				var i;
				var $item;
				var length;

				var items = itms;
				if ( !$.isArray( items ) ) {
					items = [ items ];
				}

				// this function is necessary because lint yells when a function is in a loop
				var checkIfItemMatchesValue = function checkIfItemMatchesValue( rowIndex ) {
					$item = $( this );

					data = $item.data( 'item_data' ) || {};
					if ( data[ items[ i ].property ] === items[ i ].value ) {
						selectItem( $item, items[ i ].selected, rowIndex );
					}
				};

				var selectItem = function selectItem( $itm, slct, index ) {
					var $frozenCols;

					var select = ( slct !== undefined ) ? slct : true;
					if ( select ) {
						if ( !force && selectable !== 'multi' ) {
							self.list_clearSelectedItems();
						}

						if ( !$itm.hasClass( 'selected' ) ) {
							$itm.addClass( 'selected' );

							if ( self.viewOptions.list_frozenColumns || self.viewOptions.list_selectable === 'multi' ) {
								$frozenCols = self.$element.find( '.frozen-column-wrapper tr:nth-child(' + ( index + 1 ) + ')' );

								$frozenCols.addClass( 'selected' );
								$frozenCols.find( '.repeater-select-checkbox' ).addClass( 'checked' );
							}

							if ( self.viewOptions.list_actions ) {
								self.$element.find( '.actions-column-wrapper tr:nth-child(' + ( index + 1 ) + ')' ).addClass( 'selected' );
							}

							$itm.find( 'td:first' ).prepend( '<div class="repeater-list-check"><span class="glyphicon glyphicon-ok"></span></div>' );
						}
					} else {
						if ( self.viewOptions.list_frozenColumns ) {
							$frozenCols = self.$element.find( '.frozen-column-wrapper tr:nth-child(' + ( index + 1 ) + ')' );

							$frozenCols.addClass( 'selected' );
							$frozenCols.find( '.repeater-select-checkbox' ).removeClass( 'checked' );
						}

						if ( self.viewOptions.list_actions ) {
							self.$element.find( '.actions-column-wrapper tr:nth-child(' + ( index + 1 ) + ')' ).removeClass( 'selected' );
						}

						$itm.find( '.repeater-list-check' ).remove();
						$itm.removeClass( 'selected' );
					}
				};

				if ( force === true || selectable === 'multi' ) {
					length = items.length;
				} else if ( selectable ) {
					length = ( items.length > 0 ) ? 1 : 0;
				} else {
					length = 0;
				}

				for ( i = 0; i < length; i++ ) {
					if ( items[ i ].index !== undefined ) {
						$item = this.$canvas.find( '.repeater-list .repeater-list-wrapper > table tbody tr:nth-child(' + ( items[ i ].index + 1 ) + ')' );
						if ( $item.length > 0 ) {
							selectItem( $item, items[ i ].selected, items[ i ].index );
						}
					} else if ( items[ i ].property !== undefined && items[ i ].value !== undefined ) {
						this.$canvas.find( '.repeater-list .repeater-list-wrapper > table tbody tr' ).each( checkIfItemMatchesValue );
					}
				}
			};

			$.fn.repeater.Constructor.prototype.list_sizeHeadings = function listSizeHeadings() {
				var $table = this.$element.find( '.repeater-list table' );
				$table.find( 'thead th' ).each( function eachTH() {
					var $th = $( this );
					var $heading = $th.find( '.repeater-list-heading' );
					$heading.css( {
						height: $th.outerHeight()
					} );
					$heading.outerWidth( $heading.data( 'forced-width' ) || $th.outerWidth() );
				} );
			};

			$.fn.repeater.Constructor.prototype.list_setFrozenColumns = function listSetFrozenColumns() {
				var frozenTable = this.$canvas.find( '.table-frozen' );
				var $wrapper = this.$element.find( '.repeater-canvas' );
				var $table = this.$element.find( '.repeater-list .repeater-list-wrapper > table' );
				var repeaterWrapper = this.$element.find( '.repeater-list' );
				var numFrozenColumns = this.viewOptions.list_frozenColumns;
				var self = this;

				if ( this.viewOptions.list_selectable === 'multi' ) {
					numFrozenColumns = numFrozenColumns + 1;
					$wrapper.addClass( 'multi-select-enabled' );
				}

				if ( frozenTable.length < 1 ) {
					// setup frozen column markup
					// main wrapper and remove unneeded columns
					var $frozenColumnWrapper = $( '<div class="frozen-column-wrapper"></div>' ).insertBefore( $table );
					var $frozenColumn = $table.clone().addClass( 'table-frozen' );
					$frozenColumn.find( 'th:not(:lt(' + numFrozenColumns + '))' ).remove();
					$frozenColumn.find( 'td:not(:nth-child(n+0):nth-child(-n+' + numFrozenColumns + '))' ).remove();

					// need to set absolute heading for vertical scrolling
					var $frozenThead = $frozenColumn.clone().removeClass( 'table-frozen' );
					$frozenThead.find( 'tbody' ).remove();
					var $frozenTheadWrapper = $( '<div class="frozen-thead-wrapper"></div>' ).append( $frozenThead );

					// this gets a little messy with all the cloning. We need to make sure the ID and FOR
					// attribs are unique for the 'top most' cloned checkbox
					var $checkboxLabel = $frozenTheadWrapper.find( 'th label.checkbox-custom.checkbox-inline' );
					$checkboxLabel.attr( 'id', $checkboxLabel.attr( 'id' ) + '_cloned' );

					$frozenColumnWrapper.append( $frozenColumn );
					repeaterWrapper.append( $frozenTheadWrapper );
					this.$canvas.addClass( 'frozen-enabled' );
				}

				this.list_sizeFrozenColumns();

				$( '.frozen-thead-wrapper .repeater-list-heading' ).on( 'click', function onClickHeading() {
					var index = $( this ).parent( 'th' ).index();
					index = index + 1;
					self.$element.find( '.repeater-list-wrapper > table thead th:nth-child(' + index + ') .repeater-list-heading' )[ 0 ].click();
				} );
			};

			$.fn.repeater.Constructor.prototype.list_positionColumns = function listPositionColumns() {
				var $wrapper = this.$element.find( '.repeater-canvas' );
				var scrollTop = $wrapper.scrollTop();
				var scrollLeft = $wrapper.scrollLeft();
				var frozenEnabled = this.viewOptions.list_frozenColumns || this.viewOptions.list_selectable === 'multi';
				var actionsEnabled = this.viewOptions.list_actions;
				var ltrDirection = this.viewOptions.list_direction === 'ltr';

				var canvasWidth = this.$element.find( '.repeater-canvas' ).outerWidth();
				var tableWidth = this.$element.find( '.repeater-list .repeater-list-wrapper > table' ).outerWidth();

				var actionsWidth = this.$element.find( '.table-actions' ) ? this.$element.find( '.table-actions' ).outerWidth() : 0;

				var shouldScroll = ( tableWidth - ( canvasWidth - actionsWidth ) ) >= scrollLeft;

				if ( scrollTop > 0 ) {
					$wrapper.find( '.repeater-list-heading' ).css( 'top', scrollTop );
				} else {
					$wrapper.find( '.repeater-list-heading' ).css( 'top', '0' );
				}

				if ( ltrDirection ) {
					if ( frozenEnabled ) {
						$wrapper.find( '.frozen-thead-wrapper' ).css( 'left', scrollLeft );
						$wrapper.find( '.frozen-column-wrapper' ).css( 'left', scrollLeft );
					}
					if ( actionsEnabled && shouldScroll ) {
						$wrapper.find( '.actions-thead-wrapper' ).css( 'right', -scrollLeft );
						$wrapper.find( '.actions-column-wrapper' ).css( 'right', -scrollLeft );
					}
				} else {
					if ( frozenEnabled ) {
						$wrapper.find( '.frozen-thead-wrapper' ).css( 'right', scrollLeft );
						$wrapper.find( '.frozen-column-wrapper' ).css( 'right', scrollLeft );
					}
					if ( actionsEnabled && shouldScroll ) {
						$wrapper.find( '.actions-thead-wrapper' ).css( 'left', -scrollLeft );
						$wrapper.find( '.actions-column-wrapper' ).css( 'left', -scrollLeft );
					}
				}
			};

			$.fn.repeater.Constructor.prototype.list_createItemActions = function listCreateItemActions() {
				var actionsHtml = '';
				var self = this;
				var i;
				var length;
				var $table = this.$element.find( '.repeater-list .repeater-list-wrapper > table' );
				var $actionsTable = this.$canvas.find( '.table-actions' );

				for ( i = 0, length = this.viewOptions.list_actions.items.length; i < length; i++ ) {
					var action = this.viewOptions.list_actions.items[ i ];
					var html = action.html;

					actionsHtml += '<li><a href="#" data-action="' + action.name + '" class="action-item"> ' + html + '</a></li>';
				}

				var actionsDropdown = '<div class="btn-group">' +
					'<button type="button" class="btn btn-xs btn-default dropdown-toggle repeater-actions-button" data-toggle="dropdown" data-flip="auto" aria-expanded="false">' +
					'<span class="caret"></span>' +
					'</button>' +
					'<ul class="dropdown-menu dropdown-menu-right" role="menu">' +
					actionsHtml +
					'</ul></div>';

				if ( $actionsTable.length < 1 ) {
					var $actionsColumnWrapper = $( '<div class="actions-column-wrapper" style="width: ' + this.list_actions_width + 'px"></div>' ).insertBefore( $table );
					var $actionsColumn = $table.clone().addClass( 'table-actions' );
					$actionsColumn.find( 'th:not(:last-child)' ).remove();
					$actionsColumn.find( 'tr td:not(:last-child)' ).remove();

					// Dont show actions dropdown in header if not multi select
					if ( this.viewOptions.list_selectable === 'multi' || this.viewOptions.list_selectable === 'action' ) {
						$actionsColumn.find( 'thead tr' ).html( '<th><div class="repeater-list-heading">' + actionsDropdown + '</div></th>' );

						if ( this.viewOptions.list_selectable !== 'action' ) {
							// disable the header dropdown until an item is selected
							$actionsColumn.find( 'thead .btn' ).attr( 'disabled', 'disabled' );
						}
					} else {
						var label = this.viewOptions.list_actions.label || '<span class="actions-hidden">a</span>';
						$actionsColumn.find( 'thead tr' ).addClass( 'empty-heading' ).html( '<th>' + label + '<div class="repeater-list-heading">' + label + '</div></th>' );
					}

					// Create Actions dropdown for each cell in actions table
					var $actionsCells = $actionsColumn.find( 'td' );

					$actionsCells.each( function addActionsDropdown( rowNumber ) {
						$( this ).html( actionsDropdown );
						$( this ).find( 'a' ).attr( 'data-row', rowNumber + 1 );
					} );

					$actionsColumnWrapper.append( $actionsColumn );

					this.$canvas.addClass( 'actions-enabled' );
				}

				this.list_sizeActionsTable();

				// row level actions click
				this.$element.find( '.table-actions tbody .action-item' ).on( 'click', function onBodyActionItemClick( e ) {
					if ( !self.isDisabled ) {
						var actionName = $( this ).data( 'action' );
						var row = $( this ).data( 'row' );
						var selected = {
							actionName: actionName,
							rows: [ row ]
						};
						self.list_getActionItems( selected, e );
					}
				} );
				// bulk actions click
				this.$element.find( '.table-actions thead .action-item' ).on( 'click', function onHeadActionItemClick( e ) {
					if ( !self.isDisabled ) {
						var actionName = $( this ).data( 'action' );
						var selected = {
							actionName: actionName,
							rows: []
						};
						var selector = '.repeater-list-wrapper > table .selected';

						if ( self.viewOptions.list_selectable === 'action' ) {
							selector = '.repeater-list-wrapper > table tr';
						}
						self.$element.find( selector ).each( function eachSelector( selectorIndex ) {
							selected.rows.push( selectorIndex + 1 );
						} );

						self.list_getActionItems( selected, e );
					}
				} );
			};

			$.fn.repeater.Constructor.prototype.list_getActionItems = function listGetActionItems( selected, e ) {
				var selectedObj = [];
				var actionObj = $.grep( this.viewOptions.list_actions.items, function matchedActions( actions ) {
					return actions.name === selected.actionName;
				} )[ 0 ];
				for ( var i = 0, selectedRowsL = selected.rows.length; i < selectedRowsL; i++ ) {
					var clickedRow = this.$canvas.find( '.repeater-list-wrapper > table tbody tr:nth-child(' + selected.rows[ i ] + ')' );
					selectedObj.push( {
						item: clickedRow,
						rowData: clickedRow.data( 'item_data' )
					} );
				}
				if ( selectedObj.length === 1 ) {
					selectedObj = selectedObj[ 0 ];
				}

				if ( actionObj.clickAction ) {
					var callback = function noop() {}; // for backwards compatibility. No idea why this was originally here...
					actionObj.clickAction( selectedObj, callback, e );
				}
			};

			$.fn.repeater.Constructor.prototype.list_sizeActionsTable = function listSizeActionsTable() {
				var $actionsTable = this.$element.find( '.repeater-list table.table-actions' );
				var $actionsTableHeader = $actionsTable.find( 'thead tr th' );
				var $table = this.$element.find( '.repeater-list-wrapper > table' );

				$actionsTableHeader.outerHeight( $table.find( 'thead tr th' ).outerHeight() );
				$actionsTableHeader.find( '.repeater-list-heading' ).outerHeight( $actionsTableHeader.outerHeight() );
				$actionsTable.find( 'tbody tr td:first-child' ).each( function eachFirstChild( i ) {
					$( this ).outerHeight( $table.find( 'tbody tr:eq(' + i + ') td' ).outerHeight() );
				} );
			};

			$.fn.repeater.Constructor.prototype.list_sizeFrozenColumns = function listSizeFrozenColumns() {
				var $table = this.$element.find( '.repeater-list .repeater-list-wrapper > table' );

				this.$element.find( '.repeater-list table.table-frozen tr' ).each( function eachTR( i ) {
					$( this ).height( $table.find( 'tr:eq(' + i + ')' ).height() );
				} );

				var columnWidth = $table.find( 'td:eq(0)' ).outerWidth();
				this.$element.find( '.frozen-column-wrapper, .frozen-thead-wrapper' ).width( columnWidth );
			};

			$.fn.repeater.Constructor.prototype.list_frozenOptionsInitialize = function listFrozenOptionsInitialize() {
				var $checkboxes = this.$element.find( '.frozen-column-wrapper .checkbox-inline' );
				var $headerCheckbox = this.$element.find( '.header-checkbox .checkbox-custom' );
				var $everyTable = this.$element.find( '.repeater-list table' );
				var self = this;

				// Make sure if row is hovered that it is shown in frozen column as well
				this.$element.find( 'tr.selectable' ).on( 'mouseover mouseleave', function onMouseEvents( e ) {
					var index = $( this ).index();
					index = index + 1;
					if ( e.type === 'mouseover' ) {
						$everyTable.find( 'tbody tr:nth-child(' + index + ')' ).addClass( 'hovered' );
					} else {
						$everyTable.find( 'tbody tr:nth-child(' + index + ')' ).removeClass( 'hovered' );
					}
				} );

				$headerCheckbox.checkbox();
				$checkboxes.checkbox();

				// Row checkboxes
				var $rowCheckboxes = this.$element.find( '.table-frozen tbody .checkbox-inline' );
				var $checkAll = this.$element.find( '.frozen-thead-wrapper thead .checkbox-inline input' );
				$rowCheckboxes.on( 'change', function onChangeRowCheckboxes( e ) {
					e.preventDefault();

					if ( !self.list_revertingCheckbox ) {
						if ( self.isDisabled ) {
							revertCheckbox( $( e.currentTarget ) );
						} else {
							var row = $( this ).attr( 'data-row' );
							row = parseInt( row, 10 ) + 1;
							self.$element.find( '.repeater-list-wrapper > table tbody tr:nth-child(' + row + ')' ).click();

							var numSelected = self.$element.find( '.table-frozen tbody .checkbox-inline.checked' ).length;
							if ( numSelected === 0 ) {
								$checkAll.prop( 'checked', false );
								$checkAll.prop( 'indeterminate', false );
							} else if ( numSelected === $rowCheckboxes.length ) {
								$checkAll.prop( 'checked', true );
								$checkAll.prop( 'indeterminate', false );
							} else {
								$checkAll.prop( 'checked', false );
								$checkAll.prop( 'indeterminate', true );
							}
						}
					}
				} );

				// "Check All" checkbox
				$checkAll.on( 'change', function onChangeCheckAll( e ) {
					if ( !self.list_revertingCheckbox ) {
						if ( self.isDisabled ) {
							revertCheckbox( $( e.currentTarget ) );
						} else if ( $( this ).is( ':checked' ) ) {
							self.$element.find( '.repeater-list-wrapper > table tbody tr:not(.selected)' ).click();
							self.$element.trigger( 'selected.fu.repeaterList', $checkboxes );
						} else {
							self.$element.find( '.repeater-list-wrapper > table tbody tr.selected' ).click();
							self.$element.trigger( 'deselected.fu.repeaterList', $checkboxes );
						}
					}
				} );

				function revertCheckbox( $checkbox ) {
					self.list_revertingCheckbox = true;
					$checkbox.checkbox( 'toggle' );
					delete self.list_revertingCheckbox;
				}
			};

			// ADDITIONAL DEFAULT OPTIONS
			$.fn.repeater.defaults = $.extend( {}, $.fn.repeater.defaults, {
				list_direction: 'ltr',
				list_columnRendered: null,
				list_columnSizing: true,
				list_columnSyncing: true,
				list_highlightSortedColumn: true,
				list_infiniteScroll: false,
				list_noItemsHTML: 'no items found',
				list_selectable: false,
				list_sortClearing: false,
				list_rowRendered: null,
				list_frozenColumns: 0,
				list_actions: false
			} );

			// EXTENSION DEFINITION
			$.fn.repeater.viewTypes.list = {
				cleared: function cleared() {
					if ( this.viewOptions.list_columnSyncing ) {
						this.list_sizeHeadings();
					}
				},
				dataOptions: function dataOptions( options ) {
					if ( this.list_sortDirection ) {
						options.sortDirection = this.list_sortDirection;
					}
					if ( this.list_sortProperty ) {
						options.sortProperty = this.list_sortProperty;
					}
					return options;
				},
				enabled: function enabled( helpers ) {
					if ( this.viewOptions.list_actions ) {
						if ( !helpers.status ) {
							this.$canvas.find( '.repeater-actions-button' ).attr( 'disabled', 'disabled' );
						} else {
							this.$canvas.find( '.repeater-actions-button' ).removeAttr( 'disabled' );
							toggleActionsHeaderButton.call( this );
						}
					}
				},
				initialize: function initialize( helpers, callback ) {
					this.list_sortDirection = null;
					this.list_sortProperty = null;
					this.list_specialBrowserClass = specialBrowserClass();
					this.list_actions_width = ( this.viewOptions.list_actions.width !== undefined ) ? this.viewOptions.list_actions.width : 37;
					this.list_noItems = false;
					callback();
				},
				resize: function resize() {
					sizeColumns.call( this, this.$element.find( '.repeater-list-wrapper > table thead tr' ) );
					if ( this.viewOptions.list_actions ) {
						this.list_sizeActionsTable();
					}
					if ( this.viewOptions.list_frozenColumns || this.viewOptions.list_selectable === 'multi' ) {
						this.list_sizeFrozenColumns();
					}
					if ( this.viewOptions.list_columnSyncing ) {
						this.list_sizeHeadings();
					}
				},
				selected: function selected() {
					var infScroll = this.viewOptions.list_infiniteScroll;
					var opts;

					this.list_firstRender = true;
					this.$loader.addClass( 'noHeader' );

					if ( infScroll ) {
						opts = ( typeof infScroll === 'object' ) ? infScroll : {};
						this.infiniteScrolling( true, opts );
					}
				},
				before: function before( helpers ) {
					var $listContainer = helpers.container.find( '.repeater-list' );
					var self = this;
					var $table;

					// this is a patch, it was pulled out of `renderThead`
					if ( helpers.data.count > 0 ) {
						this.list_noItems = false;
					} else {
						this.list_noItems = true;
					}

					if ( $listContainer.length < 1 ) {
						$listContainer = $( '<div class="repeater-list ' + this.list_specialBrowserClass + '" data-preserve="shallow"><div class="repeater-list-wrapper" data-infinite="true" data-preserve="shallow"><table aria-readonly="true" class="table" data-preserve="shallow" role="grid"></table></div></div>' );
						$listContainer.find( '.repeater-list-wrapper' ).on( 'scroll.fu.repeaterList', function onScrollRepeaterList() {
							if ( self.viewOptions.list_columnSyncing ) {
								self.list_positionHeadings();
							}
						} );
						if ( self.viewOptions.list_frozenColumns || self.viewOptions.list_actions || self.viewOptions.list_selectable === 'multi' ) {
							helpers.container.on( 'scroll.fu.repeaterList', function onScrollRepeaterList() {
								self.list_positionColumns();
							} );
						}

						helpers.container.append( $listContainer );
					}
					helpers.container.removeClass( 'actions-enabled actions-enabled multi-select-enabled' );

					$table = $listContainer.find( 'table' );
					renderThead.call( this, $table, helpers.data );
					renderTbody.call( this, $table, helpers.data );

					return false;
				},
				renderItem: function renderItem( helpers ) {
					renderRow.call( this, helpers.container, helpers.subset, helpers.index );
					return false;
				},
				after: function after() {
					var $sorted;

					if ( ( this.viewOptions.list_frozenColumns || this.viewOptions.list_selectable === 'multi' ) && !this.list_noItems ) {
						this.list_setFrozenColumns();
					}

					if ( this.viewOptions.list_actions && !this.list_noItems ) {
						this.list_createItemActions();
						this.list_sizeActionsTable();
					}

					if ( ( this.viewOptions.list_frozenColumns || this.viewOptions.list_actions || this.viewOptions.list_selectable === 'multi' ) && !this.list_noItems ) {
						this.list_positionColumns();
						this.list_frozenOptionsInitialize();
					}

					if ( this.viewOptions.list_columnSyncing ) {
						this.list_sizeHeadings();
						this.list_positionHeadings();
					}

					$sorted = this.$canvas.find( '.repeater-list-wrapper > table .repeater-list-heading.sorted' );
					if ( $sorted.length > 0 ) {
						this.list_highlightColumn( $sorted.data( 'fu_item_index' ) );
					}

					return false;
				}
			};
		}

		// ADDITIONAL METHODS
		var areDifferentColumns = function areDifferentColumns( oldCols, newCols ) {
			if ( !newCols ) {
				return false;
			}
			if ( !oldCols || ( newCols.length !== oldCols.length ) ) {
				return true;
			}
			for ( var i = 0, newColsL = newCols.length; i < newColsL; i++ ) {
				if ( !oldCols[ i ] ) {
					return true;
				}

				for ( var j in newCols[ i ] ) {
					if ( newCols[ i ].hasOwnProperty( j ) && oldCols[ i ][ j ] !== newCols[ i ][ j ] ) {
						return true;
					}
				}
			}
			return false;
		};

		var renderColumn = function renderColumn( $row, rows, rowIndex, columns, columnIndex ) {
			var className = columns[ columnIndex ].className;
			var content = rows[ rowIndex ][ columns[ columnIndex ].property ];
			var $col = $( '<td></td>' );
			var width = columns[ columnIndex ]._auto_width;

			var property = columns[ columnIndex ].property;
			if ( this.viewOptions.list_actions !== false && property === '@_ACTIONS_@' ) {
				content = '<div class="repeater-list-actions-placeholder" style="width: ' + this.list_actions_width + 'px"></div>';
			}

			content = ( content !== undefined ) ? content : '';

			$col.addClass( ( ( className !== undefined ) ? className : '' ) ).append( content );
			if ( width !== undefined ) {
				$col.outerWidth( width );
			}

			$row.append( $col );

			if ( this.viewOptions.list_selectable === 'multi' && columns[ columnIndex ].property === '@_CHECKBOX_@' ) {
				var checkBoxMarkup = '<label data-row="' + rowIndex + '" class="checkbox-custom checkbox-inline body-checkbox repeater-select-checkbox">' +
					'<input class="sr-only" type="checkbox"></label>';

				$col.html( checkBoxMarkup );
			}

			return $col;
		};

		var renderHeader = function renderHeader( $tr, columns, index ) {
			var chevDown = 'glyphicon-chevron-down';
			var chevron = '.glyphicon.rlc:first';
			var chevUp = 'glyphicon-chevron-up';
			var $div = $( '<div class="repeater-list-heading"><span class="glyphicon rlc"></span></div>' );
			var checkAllID = ( this.$element.attr( 'id' ) + '_' || '' ) + 'checkall';

			var checkBoxMarkup = '<div class="repeater-list-heading header-checkbox">' +
				'<label id="' + checkAllID + '" class="checkbox-custom checkbox-inline">' +
				'<input class="sr-only" type="checkbox" value="">' +
				'<span class="checkbox-label">&nbsp;</span>' +
				'</label>' +
				'</div>';

			var $header = $( '<th></th>' );
			var self = this;
			var $both;
			var className;
			var sortable;
			var $span;
			var $spans;

			$div.data( 'fu_item_index', index );
			$div.prepend( columns[ index ].label );
			$header.html( $div.html() ).find( '[id]' ).removeAttr( 'id' );

			if ( columns[ index ].property !== '@_CHECKBOX_@' ) {
				$header.append( $div );
			} else {
				$header.append( checkBoxMarkup );
			}

			$both = $header.add( $div );
			$span = $div.find( chevron );
			$spans = $span.add( $header.find( chevron ) );

			if ( this.viewOptions.list_actions && columns[ index ].property === '@_ACTIONS_@' ) {
				var width = this.list_actions_width;
				$header.css( 'width', width );
				$div.css( 'width', width );
			}

			className = columns[ index ].className;
			if ( className !== undefined ) {
				$both.addClass( className );
			}

			sortable = columns[ index ].sortable;
			if ( sortable ) {
				$both.addClass( 'sortable' );
				$div.on( 'click.fu.repeaterList', function onClickRepeaterList() {
					if ( !self.isDisabled ) {
						self.list_sortProperty = ( typeof sortable === 'string' ) ? sortable : columns[ index ].property;
						if ( $div.hasClass( 'sorted' ) ) {
							if ( $span.hasClass( chevUp ) ) {
								$spans.removeClass( chevUp ).addClass( chevDown );
								self.list_sortDirection = 'desc';
							} else if ( !self.viewOptions.list_sortClearing ) {
								$spans.removeClass( chevDown ).addClass( chevUp );
								self.list_sortDirection = 'asc';
							} else {
								$both.removeClass( 'sorted' );
								$spans.removeClass( chevDown );
								self.list_sortDirection = null;
								self.list_sortProperty = null;
							}
						} else {
							$tr.find( 'th, .repeater-list-heading' ).removeClass( 'sorted' );
							$spans.removeClass( chevDown ).addClass( chevUp );
							self.list_sortDirection = 'asc';
							$both.addClass( 'sorted' );
						}

						self.render( {
							clearInfinite: true,
							pageIncrement: null
						} );
					}
				} );
			}

			if ( columns[ index ].sortDirection === 'asc' || columns[ index ].sortDirection === 'desc' ) {
				$tr.find( 'th, .repeater-list-heading' ).removeClass( 'sorted' );
				$both.addClass( 'sortable sorted' );
				if ( columns[ index ].sortDirection === 'asc' ) {
					$spans.addClass( chevUp );
					this.list_sortDirection = 'asc';
				} else {
					$spans.addClass( chevDown );
					this.list_sortDirection = 'desc';
				}

				this.list_sortProperty = ( typeof sortable === 'string' ) ? sortable : columns[ index ].property;
			}

			$tr.append( $header );
		};

		var onClickRowRepeaterList = function onClickRowRepeaterList( repeater ) {
			var isMulti = repeater.viewOptions.list_selectable === 'multi';
			var isActions = repeater.viewOptions.list_actions;
			var $repeater = repeater.$element;

			if ( !repeater.isDisabled ) {
				var $item = $( this );
				var index = $( this ).index() + 1;
				var $frozenRow = $repeater.find( '.frozen-column-wrapper tr:nth-child(' + index + ')' );
				var $actionsRow = $repeater.find( '.actions-column-wrapper tr:nth-child(' + index + ')' );
				var $checkBox = $repeater.find( '.frozen-column-wrapper tr:nth-child(' + index + ') .checkbox-inline' );

				if ( $item.is( '.selected' ) ) {
					$item.removeClass( 'selected' );
					if ( isMulti ) {
						$checkBox.click();
						$frozenRow.removeClass( 'selected' );
						if ( isActions ) {
							$actionsRow.removeClass( 'selected' );
						}
					} else {
						$item.find( '.repeater-list-check' ).remove();
					}

					$repeater.trigger( 'deselected.fu.repeaterList', $item );
				} else {
					if ( !isMulti ) {
						repeater.$canvas.find( '.repeater-list-check' ).remove();
						repeater.$canvas.find( '.repeater-list tbody tr.selected' ).each( function deslectRow() {
							$( this ).removeClass( 'selected' );
							$repeater.trigger( 'deselected.fu.repeaterList', $( this ) );
						} );
						$item.find( 'td:first' ).prepend( '<div class="repeater-list-check"><span class="glyphicon glyphicon-ok"></span></div>' );
						$item.addClass( 'selected' );
						$frozenRow.addClass( 'selected' );
					} else {
						$checkBox.click();
						$item.addClass( 'selected' );
						$frozenRow.addClass( 'selected' );
						if ( isActions ) {
							$actionsRow.addClass( 'selected' );
						}
					}
					$repeater.trigger( 'selected.fu.repeaterList', $item );
				}

				toggleActionsHeaderButton.call( repeater );
			}
		};

		var renderRow = function renderRow( $tbody, rows, index ) {
			var $row = $( '<tr></tr>' );

			if ( this.viewOptions.list_selectable ) {
				$row.data( 'item_data', rows[ index ] );

				if ( this.viewOptions.list_selectable !== 'action' ) {
					$row.addClass( 'selectable' );
					$row.attr( 'tabindex', 0 ); // allow items to be tabbed to / focused on

					var repeater = this;
					$row.on( 'click.fu.repeaterList', function callOnClickRowRepeaterList() {
						onClickRowRepeaterList.call( this, repeater );
					} );

					// allow selection via enter key
					$row.keyup( function onRowKeyup( e ) {
						if ( e.keyCode === 13 ) {
							// triggering a standard click event to be caught by the row click handler above
							$row.trigger( 'click.fu.repeaterList' );
						}
					} );
				}
			}

			if ( this.viewOptions.list_actions && !this.viewOptions.list_selectable ) {
				$row.data( 'item_data', rows[ index ] );
			}

			var columns = [];
			for ( var i = 0, length = this.list_columns.length; i < length; i++ ) {
				columns.push( renderColumn.call( this, $row, rows, index, this.list_columns, i ) );
			}

			$tbody.append( $row );

			if ( this.viewOptions.list_columnRendered ) {
				for ( var columnIndex = 0, colLength = columns.length; columnIndex < colLength; columnIndex++ ) {
					if ( !( this.list_columns[ columnIndex ].property === '@_CHECKBOX_@' || this.list_columns[ columnIndex ].property === '@_ACTIONS_@' ) ) {
						this.viewOptions.list_columnRendered( {
							container: $row,
							columnAttr: this.list_columns[ columnIndex ].property,
							item: columns[ columnIndex ],
							rowData: rows[ index ]
						}, function noop() {} );
					}
				}
			}

			if ( this.viewOptions.list_rowRendered ) {
				this.viewOptions.list_rowRendered( {
					container: $tbody,
					item: $row,
					rowData: rows[ index ]
				}, function noop() {} );
			}
		};

		var renderTbody = function renderTbody( $table, data ) {
			var $tbody = $table.find( 'tbody' );
			var $empty;

			if ( $tbody.length < 1 ) {
				$tbody = $( '<tbody data-container="true"></tbody>' );
				$table.append( $tbody );
			}

			if ( typeof data.error === 'string' && data.error.length > 0 ) {
				$empty = $( '<tr class="empty text-danger"><td colspan="' + this.list_columns.length + '"></td></tr>' );
				$empty.find( 'td' ).append( data.error );
				$tbody.append( $empty );
			} else if ( data.items && data.items.length < 1 ) {
				$empty = $( '<tr class="empty"><td colspan="' + this.list_columns.length + '"></td></tr>' );
				$empty.find( 'td' ).append( this.viewOptions.list_noItemsHTML );
				$tbody.append( $empty );
			}
		};

		var renderThead = function renderThead( $table, data ) {
			var columns = data.columns || [];
			var $thead = $table.find( 'thead' );
			var i;
			var length;
			var $tr;

			if ( this.list_firstRender || areDifferentColumns( this.list_columns, columns ) || $thead.length === 0 ) {
				$thead.remove();

				// list_noItems is set in `before` method

				if ( this.viewOptions.list_selectable === 'multi' ) {
					var checkboxColumn = {
						label: 'c',
						property: '@_CHECKBOX_@',
						sortable: false
					};
					columns.splice( 0, 0, checkboxColumn );
				}

				this.list_columns = columns;
				this.list_firstRender = false;
				this.$loader.removeClass( 'noHeader' );

				// keep action column header even when empty, you'll need it later....
				if ( this.viewOptions.list_actions ) {
					var actionsColumn = {
						label: this.viewOptions.list_actions.label || '<span class="actions-hidden">a</span>',
						property: '@_ACTIONS_@',
						sortable: false,
						width: this.list_actions_width
					};
					columns.push( actionsColumn );
				}


				$thead = $( '<thead data-preserve="deep"><tr></tr></thead>' );
				$tr = $thead.find( 'tr' );
				for ( i = 0, length = columns.length; i < length; i++ ) {
					renderHeader.call( this, $tr, columns, i );
				}
				$table.prepend( $thead );

				if ( this.viewOptions.list_selectable === 'multi' ) {
					// after checkbox column is created need to get width of checkbox column from
					// its css class
					var checkboxWidth = this.$element.find( '.repeater-list-wrapper .header-checkbox' ).outerWidth();
					var selectColumn = $.grep( columns, function grepColumn( column ) {
						return column.property === '@_CHECKBOX_@';
					} )[ 0 ];
					selectColumn.width = checkboxWidth;
				}
				sizeColumns.call( this, $tr );
			}
		};

		var sizeColumns = function sizeColumns( $tr ) {
			var automaticallyGeneratedWidths = [];
			var self = this;
			var i;
			var length;
			var newWidth;
			var widthTaken;

			if ( this.viewOptions.list_columnSizing ) {
				i = 0;
				widthTaken = 0;
				$tr.find( 'th' ).each( function eachTH() {
					var $th = $( this );
					var width;
					if ( self.list_columns[ i ].width !== undefined ) {
						width = self.list_columns[ i ].width;
						$th.outerWidth( width );
						widthTaken += $th.outerWidth();
						self.list_columns[ i ]._auto_width = width;
					} else {
						var outerWidth = $th.find( '.repeater-list-heading' ).outerWidth();
						automaticallyGeneratedWidths.push( {
							col: $th,
							index: i,
							minWidth: outerWidth
						} );
					}

					i++;
				} );

				length = automaticallyGeneratedWidths.length;
				if ( length > 0 ) {
					var canvasWidth = this.$canvas.find( '.repeater-list-wrapper' ).outerWidth();
					newWidth = Math.floor( ( canvasWidth - widthTaken ) / length );
					for ( i = 0; i < length; i++ ) {
						if ( automaticallyGeneratedWidths[ i ].minWidth > newWidth ) {
							newWidth = automaticallyGeneratedWidths[ i ].minWidth;
						}
						automaticallyGeneratedWidths[ i ].col.outerWidth( newWidth );
						this.list_columns[ automaticallyGeneratedWidths[ i ].index ]._auto_width = newWidth;
					}
				}
			}
		};

		var specialBrowserClass = function specialBrowserClass() {
			var ua = window.navigator.userAgent;
			var msie = ua.indexOf( 'MSIE ' );
			var firefox = ua.indexOf( 'Firefox' );

			if ( msie > 0 ) {
				return 'ie-' + parseInt( ua.substring( msie + 5, ua.indexOf( '.', msie ) ), 10 );
			} else if ( firefox > 0 ) {
				return 'firefox';
			}

			return '';
		};

		var toggleActionsHeaderButton = function toggleActionsHeaderButton() {
			var selectedSelector = '.repeater-list-wrapper > table .selected';
			var $actionsColumn = this.$element.find( '.table-actions' );
			var $selected;

			if ( this.viewOptions.list_selectable === 'action' ) {
				selectedSelector = '.repeater-list-wrapper > table tr';
			}

			$selected = this.$canvas.find( selectedSelector );

			if ( $selected.length > 0 ) {
				$actionsColumn.find( 'thead .btn' ).removeAttr( 'disabled' );
			} else {
				$actionsColumn.find( 'thead .btn' ).attr( 'disabled', 'disabled' );
			}
		};



	} )( jQuery );


	( function( $ ) {

		/* global jQuery:true */

		/*
		 * Fuel UX Repeater - Thumbnail View Plugin
		 * https://github.com/ExactTarget/fuelux
		 *
		 * Copyright (c) 2014 ExactTarget
		 * Licensed under the BSD New license.
		 */



		// -- BEGIN MODULE CODE HERE --

		if ( $.fn.repeater ) {
			//ADDITIONAL METHODS
			$.fn.repeater.Constructor.prototype.thumbnail_clearSelectedItems = function() {
				this.$canvas.find( '.repeater-thumbnail-cont .selectable.selected' ).removeClass( 'selected' );
			};

			$.fn.repeater.Constructor.prototype.thumbnail_getSelectedItems = function() {
				var selected = [];
				this.$canvas.find( '.repeater-thumbnail-cont .selectable.selected' ).each( function() {
					selected.push( $( this ) );
				} );
				return selected;
			};

			$.fn.repeater.Constructor.prototype.thumbnail_setSelectedItems = function( items, force ) {
				var selectable = this.viewOptions.thumbnail_selectable;
				var self = this;
				var i, $item, l, n;

				//this function is necessary because lint yells when a function is in a loop
				function compareItemIndex() {
					if ( n === items[ i ].index ) {
						$item = $( this );
						return false;
					} else {
						n++;
					}
				}

				//this function is necessary because lint yells when a function is in a loop
				function compareItemSelector() {
					$item = $( this );
					if ( $item.is( items[ i ].selector ) ) {
						selectItem( $item, items[ i ].selected );
					}
				}

				function selectItem( $itm, select ) {
					select = ( select !== undefined ) ? select : true;
					if ( select ) {
						if ( !force && selectable !== 'multi' ) {
							self.thumbnail_clearSelectedItems();
						}

						$itm.addClass( 'selected' );
					} else {
						$itm.removeClass( 'selected' );
					}
				}

				if ( !$.isArray( items ) ) {
					items = [ items ];
				}

				if ( force === true || selectable === 'multi' ) {
					l = items.length;
				} else if ( selectable ) {
					l = ( items.length > 0 ) ? 1 : 0;
				} else {
					l = 0;
				}

				for ( i = 0; i < l; i++ ) {
					if ( items[ i ].index !== undefined ) {
						$item = $();
						n = 0;
						this.$canvas.find( '.repeater-thumbnail-cont .selectable' ).each( compareItemIndex );
						if ( $item.length > 0 ) {
							selectItem( $item, items[ i ].selected );
						}

					} else if ( items[ i ].selector ) {
						this.$canvas.find( '.repeater-thumbnail-cont .selectable' ).each( compareItemSelector );
					}
				}
			};

			//ADDITIONAL DEFAULT OPTIONS
			$.fn.repeater.defaults = $.extend( {}, $.fn.repeater.defaults, {
				thumbnail_alignment: 'left',
				thumbnail_infiniteScroll: false,
				thumbnail_itemRendered: null,
				thumbnail_noItemsHTML: 'no items found',
				thumbnail_selectable: false,
				thumbnail_template: '<div class="thumbnail repeater-thumbnail"><img height="75" src="{{src}}" width="65"><span>{{name}}</span></div>'
			} );

			//EXTENSION DEFINITION
			$.fn.repeater.viewTypes.thumbnail = {
				selected: function() {
					var infScroll = this.viewOptions.thumbnail_infiniteScroll;
					var opts;
					if ( infScroll ) {
						opts = ( typeof infScroll === 'object' ) ? infScroll : {};
						this.infiniteScrolling( true, opts );
					}
				},
				before: function( helpers ) {
					var alignment = this.viewOptions.thumbnail_alignment;
					var $cont = this.$canvas.find( '.repeater-thumbnail-cont' );
					var data = helpers.data;
					var response = {};
					var $empty, validAlignments;

					if ( $cont.length < 1 ) {
						$cont = $( '<div class="clearfix repeater-thumbnail-cont" data-container="true" data-infinite="true" data-preserve="shallow"></div>' );
						if ( alignment && alignment !== 'none' ) {
							validAlignments = {
								'center': 1,
								'justify': 1,
								'left': 1,
								'right': 1
							};
							alignment = ( validAlignments[ alignment ] ) ? alignment : 'justify';
							$cont.addClass( 'align-' + alignment );
							this.thumbnail_injectSpacers = true;
						} else {
							this.thumbnail_injectSpacers = false;
						}
						response.item = $cont;
					} else {
						response.action = 'none';
					}

					if ( data.items && data.items.length < 1 ) {
						$empty = $( '<div class="empty"></div>' );
						$empty.append( this.viewOptions.thumbnail_noItemsHTML );
						$cont.append( $empty );
					} else {
						$cont.find( '.empty:first' ).remove();
					}

					return response;
				},
				renderItem: function( helpers ) {
					var selectable = this.viewOptions.thumbnail_selectable;
					var selected = 'selected';
					var self = this;
					var $thumbnail = $( fillTemplate( helpers.subset[ helpers.index ], this.viewOptions.thumbnail_template ) );

					$thumbnail.data( 'item_data', helpers.data.items[ helpers.index ] );

					if ( selectable ) {
						$thumbnail.addClass( 'selectable' );
						$thumbnail.on( 'click', function() {
							if ( self.isDisabled ) return;

							if ( !$thumbnail.hasClass( selected ) ) {
								if ( selectable !== 'multi' ) {
									self.$canvas.find( '.repeater-thumbnail-cont .selectable.selected' ).each( function() {
										var $itm = $( this );
										$itm.removeClass( selected );
										self.$element.trigger( 'deselected.fu.repeaterThumbnail', $itm );
									} );
								}

								$thumbnail.addClass( selected );
								self.$element.trigger( 'selected.fu.repeaterThumbnail', $thumbnail );
							} else {
								$thumbnail.removeClass( selected );
								self.$element.trigger( 'deselected.fu.repeaterThumbnail', $thumbnail );
							}
						} );
					}

					helpers.container.append( $thumbnail );
					if ( this.thumbnail_injectSpacers ) {
						$thumbnail.after( '<span class="spacer">&nbsp;</span>' );
					}

					if ( this.viewOptions.thumbnail_itemRendered ) {
						this.viewOptions.thumbnail_itemRendered( {
							container: helpers.container,
							item: $thumbnail,
							itemData: helpers.subset[ helpers.index ]
						}, function() {} );
					}

					return false;
				}
			};
		}

		//ADDITIONAL METHODS
		function fillTemplate( itemData, template ) {
			var invalid = false;

			function replace() {
				var end, start, val;

				start = template.indexOf( '{{' );
				end = template.indexOf( '}}', start + 2 );

				if ( start > -1 && end > -1 ) {
					val = $.trim( template.substring( start + 2, end ) );
					val = ( itemData[ val ] !== undefined ) ? itemData[ val ] : '';
					template = template.substring( 0, start ) + val + template.substring( end + 2 );
				} else {
					invalid = true;
				}
			}

			while ( !invalid && template.search( '{{' ) >= 0 ) {
				replace( template );
			}

			return template;
		}



	} )( jQuery );


	( function( $ ) {

		/* global jQuery:true */

		/*
		 * Fuel UX Scheduler
		 * https://github.com/ExactTarget/fuelux
		 *
		 * Copyright (c) 2014 ExactTarget
		 * Licensed under the BSD New license.
		 */



		// -- BEGIN MODULE CODE HERE --

		var old = $.fn.scheduler;

		// SCHEDULER CONSTRUCTOR AND PROTOTYPE

		var Scheduler = function Scheduler( element, options ) {
			var self = this;

			this.$element = $( element );
			this.options = $.extend( {}, $.fn.scheduler.defaults, options );

			// cache elements
			this.$startDate = this.$element.find( '.start-datetime .start-date' );
			this.$startTime = this.$element.find( '.start-datetime .start-time' );

			this.$timeZone = this.$element.find( '.timezone-container .timezone' );

			this.$repeatIntervalPanel = this.$element.find( '.repeat-every-panel' );
			this.$repeatIntervalSelect = this.$element.find( '.repeat-options' );

			this.$repeatIntervalSpinbox = this.$element.find( '.repeat-every' );
			this.$repeatIntervalTxt = this.$element.find( '.repeat-every-text' );

			this.$end = this.$element.find( '.repeat-end' );
			this.$endSelect = this.$end.find( '.end-options' );
			this.$endAfter = this.$end.find( '.end-after' );
			this.$endDate = this.$end.find( '.end-on-date' );

			// panels
			this.$recurrencePanels = this.$element.find( '.repeat-panel' );


			this.$repeatIntervalSelect.selectlist();

			//initialize sub-controls
			this.$element.find( '.selectlist' ).selectlist();
			this.$startDate.datepicker( this.options.startDateOptions );

			var startDateResponse = ( typeof this.options.startDateChanged === "function" ) ? this.options.startDateChanged : this._guessEndDate;
			this.$startDate.on( 'change changed.fu.datepicker dateClicked.fu.datepicker', $.proxy( startDateResponse, this ) );

			this.$startTime.combobox();
			// init start time
			if ( this.$startTime.find( 'input' ).val() === '' ) {
				this.$startTime.combobox( 'selectByIndex', 0 );
			}

			// every 0 days/hours doesn't make sense, change if not set
			if ( this.$repeatIntervalSpinbox.find( 'input' ).val() === '0' ) {
				this.$repeatIntervalSpinbox.spinbox( {
					'value': 1,
					'min': 1,
					'limitToStep': true
				} );
			} else {
				this.$repeatIntervalSpinbox.spinbox( {
					'min': 1,
					'limitToStep': true
				} );
			}

			this.$endAfter.spinbox( {
				'value': 1,
				'min': 1,
				'limitToStep': true
			} );
			this.$endDate.datepicker( this.options.endDateOptions );
			this.$element.find( '.radio-custom' ).radio();

			// bind events: 'change' is a Bootstrap JS fired event
			this.$repeatIntervalSelect.on( 'changed.fu.selectlist', $.proxy( this.repeatIntervalSelectChanged, this ) );
			this.$endSelect.on( 'changed.fu.selectlist', $.proxy( this.endSelectChanged, this ) );
			this.$element.find( '.repeat-days-of-the-week .btn-group .btn' ).on( 'change.fu.scheduler', function( e, data ) {
				self.changed( e, data, true );
			} );
			this.$element.find( '.combobox' ).on( 'changed.fu.combobox', $.proxy( this.changed, this ) );
			this.$element.find( '.datepicker' ).on( 'changed.fu.datepicker', $.proxy( this.changed, this ) );
			this.$element.find( '.datepicker' ).on( 'dateClicked.fu.datepicker', $.proxy( this.changed, this ) );
			this.$element.find( '.selectlist' ).on( 'changed.fu.selectlist', $.proxy( this.changed, this ) );
			this.$element.find( '.spinbox' ).on( 'changed.fu.spinbox', $.proxy( this.changed, this ) );
			this.$element.find( '.repeat-monthly .radio-custom, .repeat-yearly .radio-custom' ).on( 'change.fu.scheduler', $.proxy( this.changed, this ) );
		};

		var _getFormattedDate = function _getFormattedDate( dateObj, dash ) {
			var fdate = '';
			var item;

			fdate += dateObj.getFullYear();
			fdate += dash;
			item = dateObj.getMonth() + 1; //because 0 indexing makes sense when dealing with months /sarcasm
			fdate += ( item < 10 ) ? '0' + item : item;
			fdate += dash;
			item = dateObj.getDate();
			fdate += ( item < 10 ) ? '0' + item : item;

			return fdate;
		};

		var ONE_SECOND = 1000;
		var ONE_MINUTE = ONE_SECOND * 60;
		var ONE_HOUR = ONE_MINUTE * 60;
		var ONE_DAY = ONE_HOUR * 24;
		var ONE_WEEK = ONE_DAY * 7;
		var ONE_MONTH = ONE_WEEK * 5; // No good way to increment by one month using vanilla JS. Since this is an end date, we only need to ensure that this date occurs after at least one or more repeat increments, but there is no reason for it to be exact.
		var ONE_YEAR = ONE_WEEK * 52;
		var INTERVALS = {
			secondly: ONE_SECOND,
			minutely: ONE_MINUTE,
			hourly: ONE_HOUR,
			daily: ONE_DAY,
			weekly: ONE_WEEK,
			monthly: ONE_MONTH,
			yearly: ONE_YEAR
		};

		var _incrementDate = function _incrementDate( start, end, interval, increment ) {
			return new Date( start.getTime() + ( INTERVALS[ interval ] * increment ) );
		};

		Scheduler.prototype = {
			constructor: Scheduler,

			destroy: function destroy() {
				var markup;
				// set input value attribute
				this.$element.find( 'input' ).each( function() {
					$( this ).attr( 'value', $( this ).val() );
				} );

				// empty elements to return to original markup and store
				this.$element.find( '.datepicker .calendar' ).empty();

				markup = this.$element[ 0 ].outerHTML;

				// destroy components
				this.$element.find( '.combobox' ).combobox( 'destroy' );
				this.$element.find( '.datepicker' ).datepicker( 'destroy' );
				this.$element.find( '.selectlist' ).selectlist( 'destroy' );
				this.$element.find( '.spinbox' ).spinbox( 'destroy' );
				this.$element.find( '.radio-custom' ).radio( 'destroy' );
				this.$element.remove();

				// any external bindings
				// [none]

				return markup;
			},

			changed: function changed( e, data, propagate ) {
				if ( !propagate ) {
					e.stopPropagation();
				}

				this.$element.trigger( 'changed.fu.scheduler', {
					data: ( data !== undefined ) ? data : $( e.currentTarget ).data(),
					originalEvent: e,
					value: this.getValue()
				} );
			},

			disable: function disable() {
				this.toggleState( 'disable' );
			},

			enable: function enable() {
				this.toggleState( 'enable' );
			},

			setUtcTime: function setUtcTime( day, time, offset ) {
				var dateSplit = day.split( '-' );
				var timeSplit = time.split( ':' );

				function z( n ) {
					return ( n < 10 ? '0' : '' ) + n;
				}

				var utcDate = new Date( Date.UTC( dateSplit[ 0 ], ( dateSplit[ 1 ] - 1 ), dateSplit[ 2 ], timeSplit[ 0 ], timeSplit[ 1 ], ( timeSplit[ 2 ] ? timeSplit[ 2 ] : 0 ) ) );

				if ( offset === 'Z' ) {
					utcDate.setUTCHours( utcDate.getUTCHours() + 0 );
				} else {
					var expression = [];
					expression[ 0 ] = '(.)'; // Any Single Character 1
					expression[ 1 ] = '.*?'; // Non-greedy match on filler
					expression[ 2 ] = '\\d'; // Uninteresting and ignored: d
					expression[ 3 ] = '.*?'; // Non-greedy match on filler
					expression[ 4 ] = '(\\d)'; // Any Single Digit 1

					var p = new RegExp( expression.join( '' ), [ "i" ] );
					var offsetMatch = p.exec( offset );
					if ( offsetMatch !== null ) {
						var offsetDirection = offsetMatch[ 1 ];
						var offsetInteger = offsetMatch[ 2 ];
						var modifier = ( offsetDirection === '+' ) ? 1 : -1;

						utcDate.setUTCHours( utcDate.getUTCHours() + ( modifier * parseInt( offsetInteger, 10 ) ) );
					}

				}

				var localDifference = utcDate.getTimezoneOffset();
				utcDate.setMinutes( localDifference );
				return utcDate;
			},

			// called when the end range changes
			// (Never, After, On date)
			endSelectChanged: function endSelectChanged( e, data ) {
				var selectedItem, val;

				if ( !data ) {
					selectedItem = this.$endSelect.selectlist( 'selectedItem' );
					val = selectedItem.value;
				} else {
					val = data.value;
				}

				// hide all panels
				this.$endAfter.parent().addClass( 'hidden' );
				this.$endAfter.parent().attr( 'aria-hidden', 'true' );

				this.$endDate.parent().addClass( 'hidden' );
				this.$endDate.parent().attr( 'aria-hidden', 'true' );

				if ( val === 'after' ) {
					this.$endAfter.parent().removeClass( 'hide hidden' ); // jQuery deprecated hide in 3.0. Use hidden instead. Leaving hide here to support previous markup
					this.$endAfter.parent().attr( 'aria-hidden', 'false' );
				} else if ( val === 'date' ) {
					this.$endDate.parent().removeClass( 'hide hidden' ); // jQuery deprecated hide in 3.0. Use hidden instead. Leaving hide here to support previous markup
					this.$endDate.parent().attr( 'aria-hidden', 'false' );
				}
			},

			_guessEndDate: function _guessEndDate() {
				var interval = this.$repeatIntervalSelect.selectlist( 'selectedItem' ).value;
				var end = new Date( this.$endDate.datepicker( 'getDate' ) );
				var start = new Date( this.$startDate.datepicker( 'getDate' ) );
				var increment = this.$repeatIntervalSpinbox.find( 'input' ).val();

				if ( interval !== "none" && end <= start ) {
					// if increment spinbox is hidden, user has no idea what it is set to and it is probably not set to
					// something they intended. Safest option is to set date forward by an increment of 1.
					// this will keep monthly & yearly from auto-incrementing by more than a single interval
					if ( !this.$repeatIntervalSpinbox.is( ':visible' ) ) {
						increment = 1;
					}

					// treat weekdays as weekly. This treats all "weekdays" as a single set, of which a single increment
					// is one week.
					if ( interval === "weekdays" ) {
						increment = 1;
						interval = "weekly";
					}

					end = _incrementDate( start, end, interval, increment );

					this.$endDate.datepicker( 'setDate', end );
				}
			},

			getValue: function getValue() {
				// FREQ = frequency (secondly, minutely, hourly, daily, weekdays, weekly, monthly, yearly)
				// BYDAY = when picking days (MO,TU,WE,etc)
				// BYMONTH = when picking months (Jan,Feb,March) - note the values should be 1,2,3...
				// BYMONTHDAY = when picking days of the month (1,2,3...)
				// BYSETPOS = when picking First,Second,Third,Fourth,Last (1,2,3,4,-1)

				var interval = this.$repeatIntervalSpinbox.spinbox( 'value' );
				var pattern = '';
				var repeat = this.$repeatIntervalSelect.selectlist( 'selectedItem' ).value;
				var startTime;

				if ( this.$startTime.combobox( 'selectedItem' ).value ) {
					startTime = this.$startTime.combobox( 'selectedItem' ).value;
					startTime = startTime.toLowerCase();

				} else {
					startTime = this.$startTime.combobox( 'selectedItem' ).text.toLowerCase();
				}

				var timeZone = this.$timeZone.selectlist( 'selectedItem' );
				var day, days, hasAm, hasPm, month, pos, startDateTime, type;

				startDateTime = '' + _getFormattedDate( this.$startDate.datepicker( 'getDate' ), '-' );

				startDateTime += 'T';
				hasAm = ( startTime.search( 'am' ) >= 0 );
				hasPm = ( startTime.search( 'pm' ) >= 0 );
				startTime = $.trim( startTime.replace( /am/g, '' ).replace( /pm/g, '' ) ).split( ':' );
				startTime[ 0 ] = parseInt( startTime[ 0 ], 10 );
				startTime[ 1 ] = parseInt( startTime[ 1 ], 10 );
				if ( hasAm && startTime[ 0 ] > 11 ) {
					startTime[ 0 ] = 0;
				} else if ( hasPm && startTime[ 0 ] < 12 ) {
					startTime[ 0 ] += 12;
				}

				startDateTime += ( startTime[ 0 ] < 10 ) ? '0' + startTime[ 0 ] : startTime[ 0 ];
				startDateTime += ':';
				startDateTime += ( startTime[ 1 ] < 10 ) ? '0' + startTime[ 1 ] : startTime[ 1 ];

				startDateTime += ( timeZone.offset === '+00:00' ) ? 'Z' : timeZone.offset;

				if ( repeat === 'none' ) {
					pattern = 'FREQ=DAILY;INTERVAL=1;COUNT=1;';
				} else if ( repeat === 'secondly' ) {
					pattern = 'FREQ=SECONDLY;';
					pattern += 'INTERVAL=' + interval + ';';
				} else if ( repeat === 'minutely' ) {
					pattern = 'FREQ=MINUTELY;';
					pattern += 'INTERVAL=' + interval + ';';
				} else if ( repeat === 'hourly' ) {
					pattern = 'FREQ=HOURLY;';
					pattern += 'INTERVAL=' + interval + ';';
				} else if ( repeat === 'daily' ) {
					pattern += 'FREQ=DAILY;';
					pattern += 'INTERVAL=' + interval + ';';
				} else if ( repeat === 'weekdays' ) {
					pattern += 'FREQ=WEEKLY;';
					pattern += 'BYDAY=MO,TU,WE,TH,FR;';
					pattern += 'INTERVAL=1;';
				} else if ( repeat === 'weekly' ) {
					days = [];
					this.$element.find( '.repeat-days-of-the-week .btn-group input:checked' ).each( function() {
						days.push( $( this ).data().value );
					} );

					pattern += 'FREQ=WEEKLY;';
					pattern += 'BYDAY=' + days.join( ',' ) + ';';
					pattern += 'INTERVAL=' + interval + ';';
				} else if ( repeat === 'monthly' ) {
					pattern += 'FREQ=MONTHLY;';
					pattern += 'INTERVAL=' + interval + ';';
					type = this.$element.find( 'input[name=repeat-monthly]:checked' ).val();

					if ( type === 'bymonthday' ) {
						day = parseInt( this.$element.find( '.repeat-monthly-date .selectlist' ).selectlist( 'selectedItem' ).text, 10 );
						pattern += 'BYMONTHDAY=' + day + ';';
					} else if ( type === 'bysetpos' ) {
						days = this.$element.find( '.repeat-monthly-day .month-days' ).selectlist( 'selectedItem' ).value;
						pos = this.$element.find( '.repeat-monthly-day .month-day-pos' ).selectlist( 'selectedItem' ).value;
						pattern += 'BYDAY=' + days + ';';
						pattern += 'BYSETPOS=' + pos + ';';
					}

				} else if ( repeat === 'yearly' ) {
					pattern += 'FREQ=YEARLY;';
					type = this.$element.find( 'input[name=repeat-yearly]:checked' ).val();

					if ( type === 'bymonthday' ) {
						// there are multiple .year-month classed elements in scheduler markup
						month = this.$element.find( '.repeat-yearly-date .year-month' ).selectlist( 'selectedItem' ).value;
						day = this.$element.find( '.repeat-yearly-date .year-month-day' ).selectlist( 'selectedItem' ).text;
						pattern += 'BYMONTH=' + month + ';';
						pattern += 'BYMONTHDAY=' + day + ';';
					} else if ( type === 'bysetpos' ) {
						days = this.$element.find( '.repeat-yearly-day .year-month-days' ).selectlist( 'selectedItem' ).value;
						pos = this.$element.find( '.repeat-yearly-day .year-month-day-pos' ).selectlist( 'selectedItem' ).value;
						// there are multiple .year-month classed elements in scheduler markup
						month = this.$element.find( '.repeat-yearly-day .year-month' ).selectlist( 'selectedItem' ).value;

						pattern += 'BYDAY=' + days + ';';
						pattern += 'BYSETPOS=' + pos + ';';
						pattern += 'BYMONTH=' + month + ';';
					}

				}

				var end = this.$endSelect.selectlist( 'selectedItem' ).value;
				var duration = '';

				// if both UNTIL and COUNT are not specified, the recurrence will repeat forever
				// http://tools.ietf.org/html/rfc2445#section-4.3.10
				if ( repeat !== 'none' ) {
					if ( end === 'after' ) {
						duration = 'COUNT=' + this.$endAfter.spinbox( 'value' ) + ';';
					} else if ( end === 'date' ) {
						duration = 'UNTIL=' + _getFormattedDate( this.$endDate.datepicker( 'getDate' ), '' ) + ';';
					}

				}

				pattern += duration;
				// remove trailing semicolon
				pattern = pattern.substring( pattern.length - 1 ) === ';' ? pattern.substring( 0, pattern.length - 1 ) : pattern;

				var data = {
					startDateTime: startDateTime,
					timeZone: timeZone,
					recurrencePattern: pattern
				};

				return data;
			},

			// called when the repeat interval changes
			// (None, Hourly, Daily, Weekdays, Weekly, Monthly, Yearly
			repeatIntervalSelectChanged: function repeatIntervalSelectChanged( e, data ) {
				var selectedItem, val, txt;

				if ( !data ) {
					selectedItem = this.$repeatIntervalSelect.selectlist( 'selectedItem' );
					val = selectedItem.value || "";
					txt = selectedItem.text || "";
				} else {
					val = data.value;
					txt = data.text;
				}

				// set the text
				this.$repeatIntervalTxt.text( txt );

				switch ( val.toLowerCase() ) {
					case 'hourly':
					case 'daily':
					case 'weekly':
					case 'monthly':
						this.$repeatIntervalPanel.removeClass( 'hide hidden' ); // jQuery deprecated hide in 3.0. Use hidden instead. Leaving hide here to support previous markup
						this.$repeatIntervalPanel.attr( 'aria-hidden', 'false' );
						break;
					default:
						this.$repeatIntervalPanel.addClass( 'hidden' ); // jQuery deprecated hide in 3.0. Use hidden instead. Leaving hide here to support previous markup
						this.$repeatIntervalPanel.attr( 'aria-hidden', 'true' );
						break;
				}

				// hide all panels
				this.$recurrencePanels.addClass( 'hidden' );
				this.$recurrencePanels.attr( 'aria-hidden', 'true' );

				// show panel for current selection
				this.$element.find( '.repeat-' + val ).removeClass( 'hide hidden' ); // jQuery deprecated hide in 3.0. Use hidden instead. Leaving hide here to support previous markup
				this.$element.find( '.repeat-' + val ).attr( 'aria-hidden', 'false' );

				// the end selection should only be shown when
				// the repeat interval is not "None (run once)"
				if ( val === 'none' ) {
					this.$end.addClass( 'hidden' );
					this.$end.attr( 'aria-hidden', 'true' );
				} else {
					this.$end.removeClass( 'hide hidden' ); // jQuery deprecated hide in 3.0. Use hidden instead. Leaving hide here to support previous markup
					this.$end.attr( 'aria-hidden', 'false' );
				}

				this._guessEndDate();
			},

			_parseAndSetRecurrencePattern: function( recurrencePattern, startTime ) {
				var recur = {};
				var i = 0;
				var item = '';
				var commaPatternSplit;

				var $repeatMonthlyDate, $repeatYearlyDate, $repeatYearlyDay;

				var semiColonPatternSplit = recurrencePattern.toUpperCase().split( ';' );
				for ( i = 0; i < semiColonPatternSplit.length; i++ ) {
					if ( semiColonPatternSplit[ i ] !== '' ) {
						item = semiColonPatternSplit[ i ].split( '=' );
						recur[ item[ 0 ] ] = item[ 1 ];
					}
				}

				if ( recur.FREQ === 'DAILY' ) {
					if ( recur.BYDAY === 'MO,TU,WE,TH,FR' ) {
						item = 'weekdays';
					} else {
						if ( recur.INTERVAL === '1' && recur.COUNT === '1' ) {
							item = 'none';
						} else {
							item = 'daily';
						}
					}
				} else if ( recur.FREQ === 'SECONDLY' ) {
					item = 'secondly';
				} else if ( recur.FREQ === 'MINUTELY' ) {
					item = 'minutely';
				} else if ( recur.FREQ === 'HOURLY' ) {
					item = 'hourly';
				} else if ( recur.FREQ === 'WEEKLY' ) {
					item = 'weekly';

					if ( recur.BYDAY ) {
						if ( recur.BYDAY === 'MO,TU,WE,TH,FR' ) {
							item = 'weekdays';
						} else {
							var el = this.$element.find( '.repeat-days-of-the-week .btn-group' );
							el.find( 'label' ).removeClass( 'active' );
							commaPatternSplit = recur.BYDAY.split( ',' );
							for ( i = 0; i < commaPatternSplit.length; i++ ) {
								el.find( 'input[data-value="' + commaPatternSplit[ i ] + '"]' ).prop( 'checked', true ).parent().addClass( 'active' );
							}
						}
					}
				} else if ( recur.FREQ === 'MONTHLY' ) {
					this.$element.find( '.repeat-monthly input' ).removeAttr( 'checked' ).removeClass( 'checked' );
					this.$element.find( '.repeat-monthly label.radio-custom' ).removeClass( 'checked' );
					if ( recur.BYMONTHDAY ) {
						$repeatMonthlyDate = this.$element.find( '.repeat-monthly-date' );
						$repeatMonthlyDate.find( 'input' ).addClass( 'checked' ).prop( 'checked', true );
						$repeatMonthlyDate.find( 'label.radio-custom' ).addClass( 'checked' );
						$repeatMonthlyDate.find( '.selectlist' ).selectlist( 'selectByValue', recur.BYMONTHDAY );
					} else if ( recur.BYDAY ) {
						var $repeatMonthlyDay = this.$element.find( '.repeat-monthly-day' );
						$repeatMonthlyDay.find( 'input' ).addClass( 'checked' ).prop( 'checked', true );
						$repeatMonthlyDay.find( 'label.radio-custom' ).addClass( 'checked' );
						if ( recur.BYSETPOS ) {
							$repeatMonthlyDay.find( '.month-day-pos' ).selectlist( 'selectByValue', recur.BYSETPOS );
						}

						$repeatMonthlyDay.find( '.month-days' ).selectlist( 'selectByValue', recur.BYDAY );
					}

					item = 'monthly';
				} else if ( recur.FREQ === 'YEARLY' ) {
					this.$element.find( '.repeat-yearly input' ).removeAttr( 'checked' ).removeClass( 'checked' );
					this.$element.find( '.repeat-yearly label.radio-custom' ).removeClass( 'checked' );
					if ( recur.BYMONTHDAY ) {
						$repeatYearlyDate = this.$element.find( '.repeat-yearly-date' );
						$repeatYearlyDate.find( 'input' ).addClass( 'checked' ).prop( 'checked', true );
						$repeatYearlyDate.find( 'label.radio-custom' ).addClass( 'checked' );
						if ( recur.BYMONTH ) {
							$repeatYearlyDate.find( '.year-month' ).selectlist( 'selectByValue', recur.BYMONTH );
						}

						$repeatYearlyDate.find( '.year-month-day' ).selectlist( 'selectByValue', recur.BYMONTHDAY );
					} else if ( recur.BYSETPOS ) {
						$repeatYearlyDay = this.$element.find( '.repeat-yearly-day' );
						$repeatYearlyDay.find( 'input' ).addClass( 'checked' ).prop( 'checked', true );
						$repeatYearlyDay.find( 'label.radio-custom' ).addClass( 'checked' );
						$repeatYearlyDay.find( '.year-month-day-pos' ).selectlist( 'selectByValue', recur.BYSETPOS );

						if ( recur.BYDAY ) {
							$repeatYearlyDay.find( '.year-month-days' ).selectlist( 'selectByValue', recur.BYDAY );
						}

						if ( recur.BYMONTH ) {
							$repeatYearlyDay.find( '.year-month' ).selectlist( 'selectByValue', recur.BYMONTH );
						}
					}

					item = 'yearly';
				} else {
					item = 'none';
				}

				if ( recur.COUNT ) {
					this.$endAfter.spinbox( 'value', parseInt( recur.COUNT, 10 ) );
					this.$endSelect.selectlist( 'selectByValue', 'after' );
				} else if ( recur.UNTIL ) {
					var untilSplit, untilDate;

					if ( recur.UNTIL.length === 8 ) {
						untilSplit = recur.UNTIL.split( '' );
						untilSplit.splice( 4, 0, '-' );
						untilSplit.splice( 7, 0, '-' );
						untilDate = untilSplit.join( '' );
					}

					var timeZone = this.$timeZone.selectlist( 'selectedItem' );
					var timezoneOffset = ( timeZone.offset === '+00:00' ) ? 'Z' : timeZone.offset;

					var utcEndHours = this.setUtcTime( untilDate, startTime.time24HourFormat, timezoneOffset );
					this.$endDate.datepicker( 'setDate', utcEndHours );

					this.$endSelect.selectlist( 'selectByValue', 'date' );
				} else {
					this.$endSelect.selectlist( 'selectByValue', 'never' );
				}

				this.endSelectChanged();

				if ( recur.INTERVAL ) {
					this.$repeatIntervalSpinbox.spinbox( 'value', parseInt( recur.INTERVAL, 10 ) );
				}

				this.$repeatIntervalSelect.selectlist( 'selectByValue', item );
				this.repeatIntervalSelectChanged();
			},

			_parseStartDateTime: function( startTimeISO8601 ) {
				var startTime = {};
				var startDate, startDateTimeISO8601FormatSplit, hours, minutes, period;

				startTime.time24HourFormat = startTimeISO8601.split( '+' )[ 0 ].split( '-' )[ 0 ];

				if ( startTimeISO8601.search( /\+/ ) > -1 ) {
					startTime.timeZoneOffset = '+' + $.trim( startTimeISO8601.split( '+' )[ 1 ] );
				} else if ( startTimeISO8601.search( /\-/ ) > -1 ) {
					startTime.timeZoneOffset = '-' + $.trim( startTimeISO8601.split( '-' )[ 1 ] );
				} else {
					startTime.timeZoneOffset = '+00:00';
				}

				startTime.time24HourFormatSplit = startTime.time24HourFormat.split( ':' );
				hours = parseInt( startTime.time24HourFormatSplit[ 0 ], 10 );
				minutes = ( startTime.time24HourFormatSplit[ 1 ] ) ? parseInt( startTime.time24HourFormatSplit[ 1 ].split( '+' )[ 0 ].split( '-' )[ 0 ].split( 'Z' )[ 0 ], 10 ) : 0;
				period = ( hours < 12 ) ? 'AM' : 'PM';

				if ( hours === 0 ) {
					hours = 12;
				} else if ( hours > 12 ) {
					hours -= 12;
				}

				minutes = ( minutes < 10 ) ? '0' + minutes : minutes;
				startTime.time12HourFormat = hours + ':' + minutes;
				startTime.time12HourFormatWithPeriod = hours + ':' + minutes + ' ' + period;

				return startTime;
			},

			_parseTimeZone: function( options, startTime ) {
				startTime.timeZoneQuerySelector = '';
				if ( options.timeZone ) {
					if ( typeof( options.timeZone ) === 'string' ) {
						startTime.timeZoneQuerySelector += 'li[data-name="' + options.timeZone + '"]';
					} else {
						$.each( options.timeZone, function( key, value ) {
							startTime.timeZoneQuerySelector += 'li[data-' + key + '="' + value + '"]';
						} );
					}
					startTime.timeZoneOffset = options.timeZone.offset;
				} else if ( options.startDateTime ) {
					// Time zone has not been specified via options object, therefore use the timeZoneOffset from _parseAndSetStartDateTime
					startTime.timeZoneOffset = ( startTime.timeZoneOffset === '+00:00' ) ? 'Z' : startTime.timeZoneOffset;
					startTime.timeZoneQuerySelector += 'li[data-offset="' + startTime.timeZoneOffset + '"]';
				} else {
					startTime.timeZoneOffset = 'Z';
				}

				return startTime.timeZoneOffset;
			},

			_setTimeUI: function( time12HourFormatWithPeriod ) {
				this.$startTime.find( 'input' ).val( time12HourFormatWithPeriod );
				this.$startTime.combobox( 'selectByText', time12HourFormatWithPeriod );
			},

			_setTimeZoneUI: function( querySelector ) {
				this.$timeZone.selectlist( 'selectBySelector', querySelector );
			},

			setValue: function setValue( options ) {
				var startTime = {};
				var startDateTime, startDate, startTimeISO8601, timeOffset, utcStartHours;

				// TIME
				if ( options.startDateTime ) {
					startDateTime = options.startDateTime.split( 'T' );
					startDate = startDateTime[ 0 ];
					startTimeISO8601 = startDateTime[ 1 ];

					if ( startTimeISO8601 ) {
						startTime = this._parseStartDateTime( startTimeISO8601 );
						this._setTimeUI( startTime.time12HourFormatWithPeriod );
					} else {
						startTime.time12HourFormat = '00:00';
						startTime.time24HourFormat = '00:00';
					}
				} else {
					startTime.time12HourFormat = '00:00';
					startTime.time24HourFormat = '00:00';
					var currentDate = this.$startDate.datepicker( 'getDate' );
					startDate = currentDate.getFullYear() + '-' + currentDate.getMonth() + '-' + currentDate.getDate();
				}

				// TIMEZONE
				this._parseTimeZone( options, startTime );
				if ( startTime.timeZoneQuerySelector ) {
					this._setTimeZoneUI( startTime.timeZoneQuerySelector );
				}

				// RECURRENCE PATTERN
				if ( options.recurrencePattern ) {
					this._parseAndSetRecurrencePattern( options.recurrencePattern, startTime );
				}

				utcStartHours = this.setUtcTime( startDate, startTime.time24HourFormat, startTime.timeZoneOffset );
				this.$startDate.datepicker( 'setDate', utcStartHours );
			},

			toggleState: function toggleState( action ) {
				this.$element.find( '.combobox' ).combobox( action );
				this.$element.find( '.datepicker' ).datepicker( action );
				this.$element.find( '.selectlist' ).selectlist( action );
				this.$element.find( '.spinbox' ).spinbox( action );
				this.$element.find( '.radio-custom' ).radio( action );

				if ( action === 'disable' ) {
					action = 'addClass';
				} else {
					action = 'removeClass';
				}

				this.$element.find( '.repeat-days-of-the-week .btn-group' )[ action ]( 'disabled' );
			},

			value: function value( options ) {
				if ( options ) {
					return this.setValue( options );
				} else {
					return this.getValue();
				}
			}
		};


		// SCHEDULER PLUGIN DEFINITION

		$.fn.scheduler = function scheduler( option ) {
			var args = Array.prototype.slice.call( arguments, 1 );
			var methodReturn;

			var $set = this.each( function() {
				var $this = $( this );
				var data = $this.data( 'fu.scheduler' );
				var options = typeof option === 'object' && option;

				if ( !data ) {
					$this.data( 'fu.scheduler', ( data = new Scheduler( this, options ) ) );
				}

				if ( typeof option === 'string' ) {
					methodReturn = data[ option ].apply( data, args );
				}
			} );

			return ( methodReturn === undefined ) ? $set : methodReturn;
		};

		$.fn.scheduler.defaults = {};

		$.fn.scheduler.Constructor = Scheduler;

		$.fn.scheduler.noConflict = function noConflict() {
			$.fn.scheduler = old;
			return this;
		};


		// DATA-API

		$( document ).on( 'mousedown.fu.scheduler.data-api', '[data-initialize=scheduler]', function( e ) {
			var $control = $( e.target ).closest( '.scheduler' );
			if ( !$control.data( 'fu.scheduler' ) ) {
				$control.scheduler( $control.data() );
			}
		} );

		// Must be domReady for AMD compatibility
		$( function() {
			$( '[data-initialize=scheduler]' ).each( function() {
				var $this = $( this );
				if ( $this.data( 'scheduler' ) ) return;
				$this.scheduler( $this.data() );
			} );
		} );



	} )( jQuery );


	( function( $ ) {

		/* global jQuery:true */

		/*
		 * Fuel UX Picker
		 * https://github.com/ExactTarget/fuelux
		 *
		 * Copyright (c) 2014 ExactTarget
		 * Licensed under the BSD New license.
		 */



		// -- BEGIN MODULE CODE HERE --

		var old = $.fn.picker;

		// PLACARD CONSTRUCTOR AND PROTOTYPE

		var Picker = function Picker( element, options ) {
			var self = this;
			this.$element = $( element );
			this.options = $.extend( {}, $.fn.picker.defaults, options );

			this.$accept = this.$element.find( '.picker-accept' );
			this.$cancel = this.$element.find( '.picker-cancel' );
			this.$trigger = this.$element.find( '.picker-trigger' );
			this.$footer = this.$element.find( '.picker-footer' );
			this.$header = this.$element.find( '.picker-header' );
			this.$popup = this.$element.find( '.picker-popup' );
			this.$body = this.$element.find( '.picker-body' );

			this.clickStamp = '_';

			this.isInput = this.$trigger.is( 'input' );

			this.$trigger.on( 'keydown.fu.picker', $.proxy( this.keyComplete, this ) );
			this.$trigger.on( 'focus.fu.picker', $.proxy( function inputFocus( e ) {
				if ( typeof e === "undefined" || $( e.target ).is( 'input[type=text]' ) ) {
					$.proxy( this.show(), this );
				}
			}, this ) );
			this.$trigger.on( 'click.fu.picker', $.proxy( function triggerClick( e ) {
				if ( !$( e.target ).is( 'input[type=text]' ) ) {
					$.proxy( this.toggle(), this );
				} else {
					$.proxy( this.show(), this );
				}
			}, this ) );
			this.$accept.on( 'click.fu.picker', $.proxy( this.complete, this, 'accepted' ) );
			this.$cancel.on( 'click.fu.picker', function( e ) {
				e.preventDefault();
				self.complete( 'cancelled' );
			} );


		};

		var _isOffscreen = function _isOffscreen( picker ) {
			var windowHeight = Math.max( document.documentElement.clientHeight, window.innerHeight || 0 );
			var scrollTop = $( document ).scrollTop();
			var popupTop = picker.$popup.offset();
			var popupBottom = popupTop.top + picker.$popup.outerHeight( true );

			//if the bottom of the popup goes off the page, but the top does not, dropup.
			if ( popupBottom > windowHeight + scrollTop || popupTop.top < scrollTop ) {
				return true;
			} else { //otherwise, prefer showing the top of the popup only vs the bottom
				return false;
			}
		};

		var _display = function _display( picker ) {
			picker.$popup.css( 'visibility', 'hidden' );

			_showBelow( picker );

			//if part of the popup is offscreen try to show it above
			if ( _isOffscreen( picker ) ) {
				_showAbove( picker );

				//if part of the popup is still offscreen, prefer cutting off the bottom
				if ( _isOffscreen( picker ) ) {
					_showBelow( picker );
				}
			}

			picker.$popup.css( 'visibility', 'visible' );
		};

		var _showAbove = function _showAbove( picker ) {
			picker.$popup.css( 'top', -picker.$popup.outerHeight( true ) + 'px' );
		};

		var _showBelow = function _showBelow( picker ) {
			picker.$popup.css( 'top', picker.$trigger.outerHeight( true ) + 'px' );
		};

		Picker.prototype = {
			constructor: Picker,

			complete: function complete( action ) {
				var EVENT_CALLBACK_MAP = {
					'accepted': 'onAccept',
					'cancelled': 'onCancel',
					'exited': 'onExit'
				};
				var func = this.options[ EVENT_CALLBACK_MAP[ action ] ];

				var obj = {
					contents: this.$body
				};

				if ( func ) {
					func( obj );
					this.$element.trigger( action + '.fu.picker', obj );
				} else {
					this.$element.trigger( action + '.fu.picker', obj );
					this.hide();
				}
			},

			keyComplete: function keyComplete( e ) {
				if ( this.isInput && e.keyCode === 13 ) {
					this.complete( 'accepted' );
					this.$trigger.blur();
				} else if ( e.keyCode === 27 ) {
					this.complete( 'exited' );
					this.$trigger.blur();
				}
			},

			destroy: function destroy() {
				this.$element.remove();
				// remove any external bindings
				$( document ).off( 'click.fu.picker.externalClick.' + this.clickStamp );
				// empty elements to return to original markup
				// [none]
				// return string of markup
				return this.$element[ 0 ].outerHTML;
			},

			disable: function disable() {
				this.$element.addClass( 'disabled' );
				this.$trigger.attr( 'disabled', 'disabled' );
			},

			enable: function enable() {
				this.$element.removeClass( 'disabled' );
				this.$trigger.removeAttr( 'disabled' );
			},

			toggle: function toggle() {
				if ( this.$element.hasClass( 'showing' ) ) {
					this.hide();
				} else {
					this.show();
				}
			},

			hide: function hide() {
				if ( !this.$element.hasClass( 'showing' ) ) {
					return;
				}

				this.$element.removeClass( 'showing' );
				$( document ).off( 'click.fu.picker.externalClick.' + this.clickStamp );
				this.$element.trigger( 'hidden.fu.picker' );
			},

			externalClickListener: function externalClickListener( e, force ) {
				if ( force === true || this.isExternalClick( e ) ) {
					this.complete( 'exited' );
				}
			},

			isExternalClick: function isExternalClick( e ) {
				var el = this.$element.get( 0 );
				var exceptions = this.options.externalClickExceptions || [];
				var $originEl = $( e.target );
				var i, l;

				if ( e.target === el || $originEl.parents( '.picker:first' ).get( 0 ) === el ) {
					return false;
				} else {
					for ( i = 0, l = exceptions.length; i < l; i++ ) {
						if ( $originEl.is( exceptions[ i ] ) || $originEl.parents( exceptions[ i ] ).length > 0 ) {
							return false;
						}

					}
				}

				return true;
			},

			show: function show() {
				var other;

				other = $( document ).find( '.picker.showing' );
				if ( other.length > 0 ) {
					if ( other.data( 'fu.picker' ) && other.data( 'fu.picker' ).options.explicit ) {
						return;
					}

					other.picker( 'externalClickListener', {}, true );
				}

				this.$element.addClass( 'showing' );

				_display( this );

				this.$element.trigger( 'shown.fu.picker' );

				this.clickStamp = new Date().getTime() + ( Math.floor( Math.random() * 100 ) + 1 );
				if ( !this.options.explicit ) {
					$( document ).on( 'click.fu.picker.externalClick.' + this.clickStamp, $.proxy( this.externalClickListener, this ) );
				}
			}
		};

		// PLACARD PLUGIN DEFINITION

		$.fn.picker = function picker( option ) {
			var args = Array.prototype.slice.call( arguments, 1 );
			var methodReturn;

			var $set = this.each( function() {
				var $this = $( this );
				var data = $this.data( 'fu.picker' );
				var options = typeof option === 'object' && option;

				if ( !data ) {
					$this.data( 'fu.picker', ( data = new Picker( this, options ) ) );
				}

				if ( typeof option === 'string' ) {
					methodReturn = data[ option ].apply( data, args );
				}
			} );

			return ( methodReturn === undefined ) ? $set : methodReturn;
		};

		$.fn.picker.defaults = {
			onAccept: undefined,
			onCancel: undefined,
			onExit: undefined,
			externalClickExceptions: [],
			explicit: false
		};

		$.fn.picker.Constructor = Picker;

		$.fn.picker.noConflict = function noConflict() {
			$.fn.picker = old;
			return this;
		};

		// DATA-API

		$( document ).on( 'focus.fu.picker.data-api', '[data-initialize=picker]', function( e ) {
			var $control = $( e.target ).closest( '.picker' );
			if ( !$control.data( 'fu.picker' ) ) {
				$control.picker( $control.data() );
			}
		} );

		// Must be domReady for AMD compatibility
		$( function() {
			$( '[data-initialize=picker]' ).each( function() {
				var $this = $( this );
				if ( $this.data( 'fu.picker' ) ) return;
				$this.picker( $this.data() );
			} );
		} );



	} )( jQuery );


} ) );
