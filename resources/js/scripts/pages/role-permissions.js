document.addEventListener('DOMContentLoaded', () => {
  const select = document.getElementById('object_id');
  const target = document.getElementById('js-permissions-partial-target');

  if (!select || !target) {
    return;
  }

  const roleId = select.dataset.roleId;

  select.addEventListener('change', () => {
    const nodeId = select.value;
    if (!nodeId) {
      target.innerHTML = '';
      return;
    }

    fetch(`/dashboard/roles/${roleId}/permissions/${nodeId}`, {
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        Accept: 'text/html',
      },
    })
      .then((response) => {
        if (!response.ok) {
          throw new Error('Failed to load permissions');
        }
        return response.text();
      })
      .then((html) => {
        target.innerHTML = html;
      })
      .catch(() => {
        target.innerHTML = '';
      });
  });
});
