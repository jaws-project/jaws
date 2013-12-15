function browse(textarea, url) {
    var left = (window.screen.width - 640) / 2,
        top = (window.screen.height - 480) / 2;
    the_textarea = textarea; // Should be Global
    window.open(url, '_blank', 'width=640, height=480, left=' + left + ', top=' + top + ', resizable=yes');
}
