(function (wp) {
    var el = wp.element.createElement;

    wp.hooks.addFilter('editor.BlockEdit', 'morshaus/availability', wp.compose.createHigherOrderComponent(function (BlockEdit) {
        return function (props) {
            var classes = (props.attributes.className || '').split(/\s+/).filter(Boolean);
            var isCard = props.name === 'core/cover';

            if (!isCard) return el(BlockEdit, props);

            return el(wp.element.Fragment, null,
                el(BlockEdit, props),
                el(wp.blockEditor.InspectorControls, null,
                    el(wp.components.PanelBody, {title: 'Verfügbarkeit', initialOpen: true},
                        el(wp.components.ToggleControl, {
                            label: 'Nicht verfügbar',
                            help: 'Zeigt ein Banner und sperrt die zugehörige Bestellung.',
                            checked: classes.indexOf('unverfugbar') !== -1,
                            onChange: function (unavailable) {
                                var updated = classes.filter(function (name) { return name !== 'unverfugbar'; });
                                if (unavailable) updated.push('unverfugbar');
                                props.setAttributes({className: updated.join(' ')});
                            }
                        })
                    )
                )
            );
        };
    }, 'withMorshausAvailability'));
})(window.wp);
