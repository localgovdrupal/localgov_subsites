/**
 * @file
 * Localgov Tabs behaviour.
 */

(Drupal => {
  Drupal.behaviors.localgovTabs = {
    /**
     * Attach Tabs behaviour.
     *
     * @param {object} context
     *   DOM object.
     */
    attach(context) {
      const tabs = context.querySelectorAll('[data-localgov-tabs]');

      for (let i = 0; i < tabs.length; i++) {
        this.init(tabs[i], i);
      }
    },
    /**
     * Initialise tabs.
     *
     * @param {HTMLElement} tabs
     *   Tabs element.
     * @param {integer} index
     *   Current index.
     */
    init(tabs, index) {
      const tabPanels = tabs.querySelectorAll('.tab-panel');
      const tabsInitialisedClass = 'tabs--initialised';
      const breakpoint = tabs.dataset.accordionTabsSwitch || null;
      const tabId = index;
      const mq = window.matchMedia(`(max-width: ${breakpoint})`);

      const create = () => {
        if (!tabs.classList.contains(tabsInitialisedClass)) {
          const tabPanelsNumber = tabPanels.length;

          // Only initialise tabs if there are at least 2 panels.
          if (tabPanelsNumber < 2) {
            return;
          }

          // Create tab list element and its nav wrapper.
          const tabListNav = document.createElement('nav');
          const tabList = document.createElement('ul');
          tabListNav.classList.add('tabs__nav');
          tabList.setAttribute('role', 'tablist');
          tabList.classList.add('tabs__controls');

          // Loop through all tab panels to create tab list items & controls.
          for (let i = 0; i < tabPanels.length; i++) {
            const tabListItem = document.createElement('li');
            const tab = document.createElement('button');
            const tabPanelTitle = tabPanels[i].querySelectorAll(
              '.tab-panel__title',
            )[0].textContent;
            const tabText = document.createTextNode(tabPanelTitle);

            // Add attributes & text to tab list items and tabs.
            tabListItem.setAttribute('role', 'presentation');
            tab.setAttribute('role', 'tab');
            tab.setAttribute('tabindex', -1);
            tab.setAttribute('aria-selected', false);
            tab.setAttribute('aria-controls', `tab-panel-${tabId}-${i}`);
            tab.setAttribute('id', `tab-${tabId}-${i}`);
            tab.appendChild(tabText);

            tab.addEventListener('click', e => {
              e.preventDefault();
              const isActive = e.currentTarget.getAttribute('aria-selected');
              if (isActive === 'false') {
                Drupal.behaviors.localgovTabs.switchTab(e.currentTarget, tabs);
              }
            });

            // On keydown event listener (for navigating tab controls using
            // arrow keys).
            tab.addEventListener('keydown', e => {
              let newActiveControl;

              switch (e.which) {
                case 37:
                  // Left arrow. If there's a previous element, switch to it.
                  if (i - 1 >= 0) {
                    newActiveControl = tabList
                      .querySelectorAll('li')
                      [i - 1].querySelectorAll('button');
                    Drupal.behaviors.localgovTabs.switchTab(
                      newActiveControl[0],
                      tabs,
                    );
                    newActiveControl[0].focus();
                  }
                  break;
                case 39:
                  // Right arrow. If there's a next element, switch to it.
                  if (i + 1 < tabPanelsNumber) {
                    newActiveControl = tabList
                      .querySelectorAll('li')
                      [i + 1].querySelectorAll('button');
                    Drupal.behaviors.localgovTabs.switchTab(
                      newActiveControl[0],
                      tabs,
                    );
                    newActiveControl[0].focus();
                  }
                  break;
                case 40:
                  // Arrow down. Move focus into the active panel.
                  tabPanels[i].focus();
                  break;
                default:
              }
            });

            // Add tabs to tab list items, and list items to tab list.
            tabListItem.appendChild(tab);
            tabList.appendChild(tabListItem);

            // Add attributes to tab panels.
            tabPanels[i].setAttribute('role', 'tabpanel');
            tabPanels[i].setAttribute('tabindex', '-1');
            tabPanels[i].setAttribute('aria-labelledby', `tab-${tabId}-${i}`);
            tabPanels[i].setAttribute('id', `tab-panel-${tabId}-${i}`);
          }

          // Add tab list to tabs element.
          tabListNav.append(tabList);
          tabs.insertBefore(tabListNav, tabPanels[0]);

          // Show the first panel.
          const activeControl = tabList.querySelectorAll(
            'li:first-child button',
          );
          this.switchTab(activeControl[0], tabs);

          // Add initialised class.
          tabs.classList.add(tabsInitialisedClass);
        }
      };

      const destroy = () => {
        if (tabs.classList.contains(tabsInitialisedClass)) {
          // Remove tabs.
          const tabsElements = tabs.querySelectorAll('.tabs__nav')[0];
          tabsElements.parentNode.removeChild(tabsElements);

          // Remove attributes from tab panels.
          for (let i = 0; i < tabPanels.length; i++) {
            tabPanels[i].removeAttribute('role');
            tabPanels[i].removeAttribute('tabindex');
            tabPanels[i].removeAttribute('aria-labelledby');
            tabPanels[i].removeAttribute('id');

            if (tabs.querySelectorAll('.tab-panel--active').length > 0) {
              tabs
                .querySelectorAll('.tab-panel--active')[0]
                .classList.remove('tab-panel--active');
            }
          }

          // Remove init class.
          tabs.classList.remove(tabsInitialisedClass);
        }
      };

      const breakpointCheck = function breakpointCheck() {
        if (mq.matches) {
          destroy();
        } else {
          create();
        }
      };

      // Trigger create/destroy functions at different screen widths
      // based on the value of data-accordion-tabs-switch attribute.
      if (window.matchMedia) {
        mq.addListener(() => {
          breakpointCheck();
        });
        breakpointCheck();
      }
    },
    /**
     * Switch tab.
     *
     * @param {HTMLElement} newActiveTab
     *   Tab element.
     * @param {HTMLElement} tabs
     *   Tabs element.
     */
    switchTab(newActiveTab, tabs) {
      const newActivePanelId = newActiveTab.getAttribute('aria-controls');
      const newActivePanel = tabs.querySelectorAll(`#${newActivePanelId}`);
      const activePanelClass = 'tab-panel--active';
      const oldActiveTab =
        tabs.querySelectorAll('.tabs__controls [aria-selected="true"]').length >
        0
          ? tabs.querySelectorAll('.tabs__controls [aria-selected="true"]')[0]
          : null;
      // Deactivate current active control.
      if (oldActiveTab) {
        oldActiveTab.setAttribute('aria-selected', false);
        oldActiveTab.setAttribute('tabindex', '-1');
      }
      // Set new active control.
      newActiveTab.setAttribute('aria-selected', true);
      newActiveTab.removeAttribute('tabindex');

      // Deactivate current active panel.
      if (tabs.querySelectorAll(`.${activePanelClass}`).length > 0) {
        tabs
          .querySelectorAll(`.${activePanelClass}`)[0]
          .classList.remove(activePanelClass);
      }

      // Set new active panel.
      newActivePanel[0].classList.add(activePanelClass);
      if (oldActiveTab) {
        newActivePanel[0].focus();
      }
    },
  };
})(Drupal);
