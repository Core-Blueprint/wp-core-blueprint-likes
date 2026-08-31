(() => {
  'use strict';

  const updateTarget = (targetType, targetId, data) => {
    const activeReaction = data.reaction || '';
    const counts = {
      like: String(data.like_count ?? data.count ?? 0),
      dislike: String(data.dislike_count ?? 0),
    };

    document.querySelectorAll('[data-cb-reaction-button][data-target-type][data-target-id]').forEach((button) => {
      if (button.dataset.targetType !== targetType || button.dataset.targetId !== targetId) {
        return;
      }

      const reaction = button.dataset.cbReactionButton;
      const active = reaction === activeReaction;
      button.dataset.active = active ? '1' : '0';
      button.classList.toggle('is-active', active);
      button.classList.toggle('is-liked', active && reaction === 'like');
      button.classList.toggle('is-disliked', active && reaction === 'dislike');
      button.setAttribute('aria-pressed', active ? 'true' : 'false');
      button.setAttribute('aria-label', active ? button.dataset.ariaActive : button.dataset.ariaInactive);

      const label = button.querySelector('.cb-reaction-button__label');
      if (label) {
        label.textContent = active ? button.dataset.activeLabel : button.dataset.label;
      }

      const count = button.querySelector(`[data-cb-reaction-count="${reaction}"]`);
      if (count) {
        count.textContent = counts[reaction] ?? '0';
      }
    });

    document.querySelectorAll('[data-cb-reaction-count][data-target-type][data-target-id]').forEach((count) => {
      if (count.dataset.targetType !== targetType || count.dataset.targetId !== targetId) {
        return;
      }
      const reaction = count.dataset.cbReactionCount;
      count.textContent = counts[reaction] ?? '0';
    });
  };

  const showLoggedOutMessage = (button) => {
    const targetType = button.dataset.targetType || '';
    const targetId = button.dataset.targetId || '';
    const messageText = button.dataset.loggedOutMessage || '';
    if (!messageText) {
      return;
    }

    let message = null;
    document.querySelectorAll('[data-cb-reaction-login-message]').forEach((candidate) => {
      if (candidate.dataset.targetType === targetType && candidate.dataset.targetId === targetId) {
        message = candidate;
      }
    });

    if (!message) {
      message = document.createElement('span');
      message.className = 'cb-reaction-login-message';
      message.dataset.cbReactionLoginMessage = '1';
      message.dataset.targetType = targetType;
      message.dataset.targetId = targetId;
      message.setAttribute('role', 'status');
      message.setAttribute('aria-live', 'polite');
      button.insertAdjacentElement('afterend', message);
    }

    message.textContent = messageText;
  };

  document.addEventListener('click', async (event) => {
    const button = event.target.closest('[data-cb-reaction-button]');
    if (!button || button.disabled || button.dataset.busy === '1') {
      return;
    }

    if (!CBLikes.loggedIn) {
      showLoggedOutMessage(button);
      return;
    }

    const reaction = button.dataset.cbReactionButton;
    const current = button.dataset.active === '1';
    const targetType = button.dataset.targetType;
    const targetId = button.dataset.targetId;

    button.dataset.busy = '1';
    button.setAttribute('aria-busy', 'true');

    try {
      const response = await fetch(CBLikes.endpoint, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'Content-Type': 'application/json',
          'X-WP-Nonce': CBLikes.nonce,
        },
        body: JSON.stringify({
          target_type: targetType,
          target_id: Number(targetId),
          reaction: current ? '' : reaction,
        }),
      });
      const data = await response.json();
      if (!response.ok) {
        throw new Error(data?.message || CBLikes.error);
      }

      updateTarget(targetType, targetId, data);
      button.dispatchEvent(new CustomEvent('cb:likes:changed', { bubbles: true, detail: data }));
    } catch (error) {
      console.error('[Core Blueprint Likes]', error);
    } finally {
      delete button.dataset.busy;
      button.removeAttribute('aria-busy');
    }
  });
})();
