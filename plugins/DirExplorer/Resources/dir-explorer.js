function browse(textarea, url) {
    var left = (window.screen.width - 800) / 2,
        top = (window.screen.height - 400) / 2;
    the_textarea = textarea; // Should be Global
    window.open(url, '_blank', 'width=800, height=400, left=' + left + ', top=' + top + ', resizable=yes');
}
