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

  // Notifikační dropdown pro recenzenty
  const notificationsRoot = document.querySelector('[data-notifications-root]');
  if (notificationsRoot) {
    const toggleBtn = notificationsRoot.querySelector('[data-notifications-toggle]');
    const dropdown = notificationsRoot.querySelector('[data-notifications-dropdown]');
    const listEl = notificationsRoot.querySelector('[data-notifications-list]');
    const badgeEl = notificationsRoot.querySelector('[data-notifications-badge]');
    const statusEl = notificationsRoot.querySelector('[data-notifications-status]');
    const endpoint = notificationsRoot.getAttribute('data-endpoint');

    let loaded = false;
    let loading = false;

    const setStatus = (text) => {
      if (statusEl) {
        statusEl.textContent = text;
      }
    };

    const renderList = (items) => {
      if (!listEl) {
        return;
      }

      if (!items || items.length === 0) {
        listEl.innerHTML = '<div class="notification-empty">Žádná upozornění</div>';
        if (badgeEl) badgeEl.textContent = '0';
        return;
      }

      const unreadCount = items.filter((item) => !item.is_read).length;
      if (badgeEl) {
        badgeEl.textContent = unreadCount > 9 ? '9+' : String(unreadCount);
      }

      const rows = items.map((item) => {
        const created = item.created_at_human ?? '';
        const message = item.message ?? '';
        const postTitle = item.post_title ? `Článek: ${item.post_title}` : '';
        const rowClass = item.is_read ? '' : 'notification-row--unread';
        return `
          <tr class="${rowClass}">
            <td>${created}</td>
            <td>
              <p class="notification-message">${message}</p>
              ${postTitle ? `<p class="notification-meta">${postTitle}</p>` : ''}
            </td>
          </tr>
        `;
      }).join('');

      listEl.innerHTML = `
        <table class="notification-table">
          <tbody>${rows}</tbody>
        </table>
      `;
    };

    const fetchNotifications = async () => {
      if (!endpoint) return;
      loading = true;
      setStatus('Načítám...');
      try {
        const response = await fetch(endpoint, {
          headers: {
            'Accept': 'application/json'
          },
          credentials: 'same-origin'
        });

        if (!response.ok) {
          throw new Error(`HTTP ${response.status}`);
        }

        const json = await response.json();
        if (json.status === 'ok') {
          renderList(json.data || []);
          setStatus('Aktualizováno');
          loaded = true;
        } else {
          renderList([]);
          setStatus('Chyba při načítání');
        }
      } catch (error) {
        console.error('Chyba načítání upozornění:', error);
        renderList([]);
        setStatus('Nepodařilo se načíst data');
      } finally {
        loading = false;
      }
    };

    const closeDropdown = () => {
      if (dropdown) {
        dropdown.classList.remove('is-open');
      }
      if (toggleBtn) {
        toggleBtn.setAttribute('aria-expanded', 'false');
      }
    };

    if (toggleBtn && dropdown) {
      toggleBtn.addEventListener('click', (event) => {
        event.stopPropagation();
        const isOpen = dropdown.classList.toggle('is-open');
        toggleBtn.setAttribute('aria-expanded', String(isOpen));
        if (isOpen && !loaded && !loading) {
          fetchNotifications();
        }
      });

      document.addEventListener('click', (event) => {
        if (!notificationsRoot.contains(event.target)) {
          closeDropdown();
        }
      });
    }
  }
});

