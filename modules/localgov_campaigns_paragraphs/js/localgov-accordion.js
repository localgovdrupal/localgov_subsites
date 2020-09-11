/**
 * @file
 * Accordion behaviour.
 */

(function () {
  Drupal.behaviors.accordion = {
    /**
     * Attach accordion behaviour.
     *
     * @param {object} context
     *   DOM object.
     */
    attach: function attach(context) {
      var _this = this;
      var accordions = context.querySelectorAll('.accordion');

      for (var i = 0; i < accordions.length; i++) {
        _this.init(accordions[i], i);
      }
    },

    /**
     * Initialise accordion.
     *
     * @param {HTMLElement} accordion
     *   Accordion element.
     * @param {number} index
     *   Accordion element index.
     */
    init: function init(accordion, index) {
      var accordionPanes = accordion.querySelectorAll('.accordion-pane');
      var numberOfPanes = accordionPanes.length;
      var initClass = 'accordion--initialised';

      // Only initialise accordion if it hasn't already been done.
      if (accordion.classList.contains(initClass)) {
        return;
      }

      for (var i = 0; i < numberOfPanes; i++) {
        var pane = accordionPanes[i];
        var content = pane.querySelectorAll('.accordion-pane__content');
        var title = pane.querySelectorAll('.accordion-pane__title');
        var titleText = title[0].textContent;
        var button = document.createElement('button');
        var text = document.createTextNode(titleText);
        var id = 'accordion-content-' + index + '-' + i;
        var openClass = 'accordion-pane__content--open';

        // Add id attribute to all pane content elements.
        content[0].setAttribute('id', id);

        // Add show/hide button to each accordion title.
        button.appendChild(text);
        button.setAttribute('aria-expanded', 'false');
        button.setAttribute('aria-controls', id);

        // Add click event listener to the show/hide button.
        button.addEventListener('click', function (e) {
          var targetPaneId = e.target.getAttribute('aria-controls');
          var targetPane = accordion.querySelectorAll('#' + targetPaneId);
          var openPane = accordion.querySelectorAll('.' + openClass);

          // Check the current state of the button and the content it controls.
          if (e.target.getAttribute('aria-expanded') === 'false') {
            // Close currently open pane.
            if (openPane.length) {
              var openPaneId = openPane[0].getAttribute('id');
              var openPaneButton = accordion.querySelectorAll('[aria-controls="' + openPaneId + '"]');
              openPane[0].classList.remove(openClass);
              openPaneButton[0].setAttribute('aria-expanded', 'false');
            }

            // Show new pane.
            e.target.setAttribute('aria-expanded', 'true');
            targetPane[0].classList.add(openClass);
          }
          else {
            // If target pane is currently open, close it.
            e.target.setAttribute('aria-expanded', 'false');
            targetPane[0].classList.remove(openClass);
          }
        });

        // Add show/hide button to each accordion pane title element.
        title[0].children[0].innerHTML = '';
        title[0].children[0].appendChild(button);

        // Add init class.
        accordion.classList.add(initClass);
      }
    }
  };
})();
