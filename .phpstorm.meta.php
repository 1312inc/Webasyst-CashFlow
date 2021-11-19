<?php

namespace PHPSTORM_META {

    override(
        \cashConfig::getEntityFactory(),
        map([
            '' => '@Factory',
        ])
    );
    override(
        \cashConfig::getModel(),
        map([
            '' => '@Model',
        ])
    );
    override(
        \cashConfig::getEntityRepository(),
        map([
            '' => '@Repository',
        ])
    );
    override(\cashApiAbstractMethod::fillRequestWithParams(), arg(0));
}
