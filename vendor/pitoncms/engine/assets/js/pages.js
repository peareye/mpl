/**
 * PitonCMS (https://github.com/PitonCMS)
 *
 * @link      https://github.com/PitonCMS/Piton
 * @copyright Copyright 2018 Wolfgang Moritz
 * @license   https://github.com/PitonCMS/Piton/blob/master/LICENSE (MIT License)
 */

/**
 * Manage Content JS
 */

import "./modules/main.js";
import { pitonConfig } from './modules/config.js';
import { setQueryRequestPath } from "./modules/filter.js";

setQueryRequestPath(pitonConfig.routes.adminPageGet);