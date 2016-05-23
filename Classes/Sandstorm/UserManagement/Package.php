<?php
namespace Sandstorm\UserManagement;

use TYPO3\Flow\Package\Package as BasePackage;

/**
 * The Sandstorm\UserManagement Package
 *
 */
class Package extends BasePackage {

    // TODO: Is this needed?
//    const AUTHENTICATION_PROVIDER = 'ApplicationAuthenticationProvider';

    /**
     * Invokes custom PHP code directly after the package manager has been initialized.
     *
     * @param \TYPO3\Flow\Core\Bootstrap $bootstrap The current bootstrap
     * @return void
     */
    public function boot(\TYPO3\Flow\Core\Bootstrap $bootstrap) {
        //empty
    }
}
?>
