function GATiming(title) {
    if(window.performance && window.ga) {
        var timeSincePageLoad = Math.round(window.performance.now());
        ga("send", "timing", "pageLoad", title, timeSincePageLoad);
    }
}
