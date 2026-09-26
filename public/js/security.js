document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.permission-group-head').forEach((head) => {
    head.addEventListener('click', () => {
      const group = head.parentElement.querySelector('.permission-list');
      if (group) group.hidden = !group.hidden;
    });
  });
});
