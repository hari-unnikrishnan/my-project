document.addEventListener('DOMContentLoaded', () => {
  const body = document.body;
  const sidebar = document.querySelector('.sidebar');
  const sidebarToggle = document.querySelector('[data-sidebar-toggle]');
  const themeToggle = document.querySelector('[data-theme-toggle]');
  const themeChoices = document.querySelectorAll('[data-theme-choice]');
  const chatForm = document.querySelector('[data-chat-form]');
  const chatInput = document.querySelector('[data-chat-input]');
  const chatMessages = document.querySelector('[data-chat-messages]');
  const modal = document.querySelector('[data-modal]');
  const status = document.createElement('div');

  status.className = 'preview-status';
  status.setAttribute('role', 'status');
  status.setAttribute('aria-live', 'polite');
  document.body.appendChild(status);

  const notify = (message) => {
    status.textContent = message;
    status.classList.add('show');
    window.clearTimeout(notify.timeout);
    notify.timeout = window.setTimeout(() => status.classList.remove('show'), 3200);
  };

  const applyTheme = (theme) => {
    body.classList.toggle('theme-light', theme === 'light');
    body.classList.toggle('theme-dark', theme !== 'light');
    if (themeToggle) {
      themeToggle.innerHTML = theme === 'light'
        ? '<i class="bi bi-moon-stars-fill"></i>'
        : '<i class="bi bi-sun-fill"></i>';
      themeToggle.setAttribute('aria-label', theme === 'light' ? 'Switch to dark mode' : 'Switch to light mode');
    }
    themeChoices.forEach((choice) => {
      choice.classList.toggle('active', choice.dataset.themeChoice === theme);
    });
  };

  applyTheme(localStorage.getItem('aether-theme') || 'dark');

  if (sidebarToggle && sidebar) {
    const sidebarBackdrop = document.createElement('button');
    sidebarBackdrop.className = 'sidebar-backdrop';
    sidebarBackdrop.type = 'button';
    sidebarBackdrop.setAttribute('aria-label', 'Close navigation');
    document.body.appendChild(sidebarBackdrop);

    sidebarToggle.addEventListener('click', () => {
      if (window.matchMedia('(max-width: 860px)').matches) {
        body.classList.toggle('sidebar-open');
        sidebarToggle.setAttribute('aria-expanded', String(body.classList.contains('sidebar-open')));
        return;
      }

      sidebar.classList.toggle('collapsed');
      sidebarToggle.setAttribute('aria-expanded', String(!sidebar.classList.contains('collapsed')));
    });

    sidebarBackdrop.addEventListener('click', () => {
      body.classList.remove('sidebar-open');
      sidebarToggle.setAttribute('aria-expanded', 'false');
    });
  }

  document.querySelectorAll('.sidebar .nav-item').forEach((link) => {
    link.addEventListener('click', () => {
      if (window.matchMedia('(max-width: 860px)').matches) {
        body.classList.remove('sidebar-open');
      }
    });
  });

  if (themeToggle) {
    themeToggle.addEventListener('click', () => {
      const theme = body.classList.contains('theme-light') ? 'dark' : 'light';
      localStorage.setItem('aether-theme', theme);
      applyTheme(theme);
    });
  }

  themeChoices.forEach((choice) => {
    choice.addEventListener('click', () => {
      const theme = choice.dataset.themeChoice;
      if (theme !== 'light' && theme !== 'dark') return;
      localStorage.setItem('aether-theme', theme);
      applyTheme(theme);
    });
  });

  document.querySelectorAll('[data-suggest]').forEach((button) => {
    button.addEventListener('click', () => {
      if (!chatInput) return;
      chatInput.value = button.dataset.suggest || button.textContent.trim();
      chatInput.focus();
    });
  });

  if (chatForm && chatInput && chatMessages) {
    chatForm.addEventListener('submit', (event) => {
      event.preventDefault();
      const message = chatInput.value.trim();
      if (!message) return;

      const row = document.createElement('div');
      row.className = 'message message-user';
      const bubble = document.createElement('div');
      bubble.className = 'message-bubble';
      const text = document.createElement('p');
      text.textContent = message;
      bubble.appendChild(text);
      row.appendChild(bubble);
      chatMessages.appendChild(row);
      chatInput.value = '';
      chatMessages.scrollTop = chatMessages.scrollHeight;
      notify('This standalone HTML is a visual preview. Use the Laravel app for saved conversations and AI responses.');
    });
  }

  document.querySelectorAll('[data-preview-form]').forEach((form) => {
    form.addEventListener('submit', (event) => {
      event.preventDefault();
      if (!form.reportValidity()) return;

      const password = form.querySelector('[name="password"]');
      const confirmation = form.querySelector('[name="password_confirmation"]');
      if (password && confirmation && password.value !== confirmation.value) {
        confirmation.setCustomValidity('Passwords do not match.');
        confirmation.reportValidity();
        confirmation.setCustomValidity('');
        return;
      }

      notify('This standalone HTML is a visual preview. Use the Laravel app for account-backed actions and saved data.');
    });
  });

  document.querySelectorAll('[data-open-modal]').forEach((button) => {
    button.addEventListener('click', () => {
      if (modal) modal.classList.add('show');
    });
  });

  document.querySelectorAll('[data-modal-close]').forEach((button) => {
    button.addEventListener('click', () => modal?.classList.remove('show'));
  });

  if (modal) {
    modal.addEventListener('click', (event) => {
      if (event.target === modal) modal.classList.remove('show');
    });
  }

  document.querySelectorAll('.code-block .mini-action').forEach((button) => {
    button.addEventListener('click', async () => {
      const code = button.closest('.code-block')?.querySelector('pre')?.textContent;
      if (!code) return;
      try {
        await navigator.clipboard.writeText(code);
        notify('Code copied to clipboard.');
      } catch {
        notify('Clipboard access is unavailable in this browser context.');
      }
    });
  });

  document.querySelectorAll('[data-copy-output]').forEach((button) => {
    button.addEventListener('click', async () => {
      const output = button.closest('.tool-output')?.innerText;
      if (!output) return;
      try {
        await navigator.clipboard.writeText(output);
        notify('Output copied to clipboard.');
      } catch {
        notify('Clipboard access is unavailable in this browser context.');
      }
    });
  });

  document.querySelectorAll('[data-export-table]').forEach((button) => {
    button.addEventListener('click', () => {
      const table = document.querySelector('.activity-panel table');
      if (!table) {
        notify('There is no table to export.');
        return;
      }

      const csv = Array.from(table.rows, (row) => Array.from(row.cells, (cell) => {
        const value = cell.innerText.replaceAll('"', '""');
        return `"${value}"`;
      }).join(',')).join('\r\n');
      const blob = new Blob([csv], { type: 'text/csv;charset=utf-8' });
      const link = document.createElement('a');
      link.href = URL.createObjectURL(blob);
      link.download = 'aether-preview.csv';
      link.click();
      URL.revokeObjectURL(link.href);
    });
  });
});
