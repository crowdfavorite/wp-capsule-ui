require.config({
    baseUrl: requirejsL10n.capsule,
    enforceDefine: true,
    paths: {
        "cf": requirejsL10n.capsule,
        "ace": requirejsL10n.ace,
    },
    urlArgs: "ver=" + requirejsL10n.cachebust
});
