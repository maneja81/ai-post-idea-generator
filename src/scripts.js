import Alpine from "alpinejs";
window.Alpine = Alpine;
Alpine.start();

jQuery(document).ready(function ($) {
  $(".mawp-settings-form").on("submit", function (e) {
    e.preventDefault();
    $(".form-success").hide();
    $(".form-errors").hide();
    let form = $(this);
    let data = form.serializeArray();
    let formData = {};
    let buttonText = $('.mawp-settings-form input[type="submit"]').val();
    $('.mawp-settings-form input[type="submit"]').val("Please wait..");
    $('.mawp-settings-form input[type="submit"]').attr("disabled", true);
    data.forEach((item) => {
      formData[item.name] = item.value;
    });
    $.ajax({
      url: mawp.ajax_url,
      method: "POST",
      data: {
        action: "ai_post_idea_generator_save_api_key",
        data: data,
        _ajax_nonce: mawp.nonce,
      },
      success: function (response) {
        if (response.success) {
          $(".form-success p").html(response.message);
          $(".form-success").show();
        } else {
          $(".form-errors p").html(response.message);
          $(".form-errors").show();
        }
        $('.mawp-settings-form input[type="submit"]').val(buttonText);
        $('.mawp-settings-form input[type="submit"]').attr("disabled", false);
      },
      error: function () {
        $(".form-errors").show();
        $('.mawp-settings-form input[type="submit"]').val(buttonText);
        $('.mawp-settings-form input[type="submit"]').attr("disabled", false);
      },
    });
  });

  $(".mawp-generate-ideas").on("submit", function (e) {
    e.preventDefault();
    $(".form-success").hide();
    $(".form-errors").hide();
    let form = $(this);
    let data = form.serializeArray();
    let formData = {};
    let buttonText = $('.mawp-generate-ideas input[type="submit"]').val();
    $('.mawp-generate-ideas input[type="submit"]').val("Please wait..");
    $('.mawp-generate-ideas input[type="submit"]').attr("disabled", true);
    data.forEach((item) => {
      formData[item.name] = item.value;
    });
    $.ajax({
      url: mawp.ajax_url,
      method: "POST",
      data: {
        action: "ai_post_idea_generator_generate_ideas",
        data: data,
        _ajax_nonce: mawp.nonce,
      },
      success: function (response) {
        if (response.success) {
          window.location.reload();
        } else {
          $(".form-errors p").html(response.message);
          $(".form-errors").show();
        }
        $('.mawp-generate-ideas input[type="submit"]').val(buttonText);
        $('.mawp-generate-ideas input[type="submit"]').attr("disabled", false);
      },
      error: function () {
        $(".form-errors").show();
        $('.mawp-generate-ideas input[type="submit"]').val(buttonText);
        $('.mawp-generate-ideas input[type="submit"]').attr("disabled", false);
      },
    });
  });

  $(".mawp-create-drafts").on("submit", function (e) {
    e.preventDefault();
    $(".form-success").hide();
    $(".form-errors").hide();
    let form = $(this);
    let data = form.serializeArray();
    let formData = {};
    let buttonText = $('.mawp-create-drafts input[type="submit"]').val();
    $('.mawp-create-drafts input[type="submit"]').val("Please wait..");
    $('.mawp-create-drafts input[type="submit"]').attr("disabled", true);
    data.forEach((item) => {
      formData[item.name] = item.value;
    });
    $.ajax({
      url: mawp.ajax_url,
      method: "POST",
      data: {
        action: "ai_post_idea_generator_create_drafts",
        data: data,
        _ajax_nonce: mawp.nonce,
      },
      success: function (response) {
        if (response.success) {
          $(".form-success p").html(response.message);
          $(".form-success").show();
          window.location.reload();
        } else {
          $(".form-errors p").html(response.message);
          $(".form-errors").show();
        }
        $('.mawp-create-drafts input[type="submit"]').val(buttonText);
        $('.mawp-create-drafts input[type="submit"]').attr("disabled", false);
      },
      error: function () {
        $(".form-errors").show();
        $('.mawp-create-drafts input[type="submit"]').val(buttonText);
        $('.mawp-create-drafts input[type="submit"]').attr("disabled", false);
      },
    });
  });
});
