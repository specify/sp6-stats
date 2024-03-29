$(function () {
  const select_fields = $('select');
  const result = $('#result');
  const alert = $('#alert');

  select_fields.on('change', function () {
    alert.show();

    const select_field = $(this);
    const category_name = select_field.attr('name');
    const selected_field = select_field.find('option:selected').val();

    if (selected_field === '') return true;

    const x_http = new XMLHttpRequest();
    x_http.onreadystatechange = function () {
      if (this.readyState === 4 && this.status === 200) {
        alert.hide();

        result.html(x_http.responseText);
      }
    };
    x_http.open(
      'GET',
      link +
        'chart/?collection_number=' +
        collection_number +
        '&category_name=' +
        category_name +
        '&selected_field=' +
        selected_field,
      true
    );
    x_http.send();

    select_fields.each(function () {
      const el = $(this);

      if (!el.is(select_field)) el.val('');
    });
  });

  $('select[name="database_stats"]').change();
});
