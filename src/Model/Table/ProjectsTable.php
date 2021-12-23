<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\Cache\Cache;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validator;

/**
 * Projects Model
 *
 * @method \App\Model\Entity\Project newEmptyEntity()
 * @method \App\Model\Entity\Project get(mixed $id, array $options = [])
 * @method \App\Model\Entity\Project patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @property \Cake\ORM\Association\BelongsTo $Companies
 * @property \App\Model\Table\CategoriesTable $Categories
 * @property \Cake\ORM\Association\HasMany $QtiesTags
 * @property \Cake\ORM\Association\HasMany $Templates
 * @property \Cake\ORM\Association\HasMany $Variables
 * @property \Cake\ORM\Association\BelongsToMany $Users
 */
class ProjectsTable extends Table
{
    protected const THUMB_SIZE = 50;

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('projects');
        $this->setDisplayField('title');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->hasMany('Categories', [
            'foreignKey' => 'project_id',
        ]);
        $this->hasMany('QtiesTags', [
            'foreignKey' => 'project_id',
        ]);
        $this->hasMany('Templates', [
            'foreignKey' => 'project_id',
        ]);
        $this->hasMany('Variables', [
            'foreignKey' => 'project_id',
        ]);

        $this->hasMany('ProjectsUsers', [
            'foreignKey' => 'project_id',
        ]);

        $this->belongsToMany('Users', [
            'className' => 'Users',
            'foreignKey' => 'project_id',
            'targetForeignKey' => 'user_id',
            'joinTable' => 'projects_users',
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->uuid('id')
            ->allowEmptyString('id', 'create');

        $validator
            ->allowEmptyString('no');

        $validator
            ->notEmptyString('title');

        $validator
            ->allowEmptyString('subtitle');

        $validator
            ->allowEmptyString('dat_place');

        $validator
            ->allowEmptyString('descript');

        $validator
            ->boolean('active')
            ->requirePresence('active', 'create')
            ->notEmptyString('active');

        $validator
            ->allowEmptyString('investor_title');

        $validator
            ->allowEmptyString('investor_address');

        $validator
            ->allowEmptyString('investor_zip');

        $validator
            ->allowEmptyString('creator_title');

        $validator
            ->allowEmptyString('creator_address');

        $validator
            ->allowEmptyString('creator_zip');

        $validator
            ->allowEmptyString('creator_post');

        $validator
            ->allowEmptyString('creator_person');

        $validator
            ->allowEmptyString('investor_post');

        $validator
            ->allowEmptyString('notes');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        return $rules;
    }

    /**
     * getTitle method
     *
     * @param string $id Project id
     * @return string
     */
    public function getTitle($id)
    {
        $ret = $this
            ->find()
            ->select(['title'])
            ->where(['id' => $id])
            ->map(function ($row) {

                return $row->title;
            })
            ->toArray();

        return reset($ret);
    }

    /**
     * Returns projects for specified user
     *
     * @param string $userId User id
     * @param bool $mapUserData Map user data
     * @return mixed
     */
    public function findForUser($userId, $mapUserData = true)
    {
        $query = $this->find();

        $query
            ->matching('ProjectsUsers', function (\Cake\ORM\Query $q) use ($userId) {
                return $q->where(['ProjectsUsers.user_id' => $userId]);
            })
            ->order(['ProjectsUsers.archived', 'Projects.no DESC']);

        if ($mapUserData) {
            return $query->map(function ($row) {
                $row->userData = $row->_matchingData['ProjectsUsers'];
                unset($row->_matchingData);

                return $row;
            });
        } else {
            return $query;
        }
    }

    /**
     * Returns Project variables
     *
     * @param string $id Project id
     * @return array Project variables
     */
    public function getVariables($id)
    {
        $ret = [];

        $variables = Cache::read('variable.Project' . $id, 'site');
        if (!empty($variables)) {
            $ret = $variables;
        } else {
            $Variables = TableRegistry::getTableLocator()->get('Variables');
            $vars = $Variables->find()
                ->select(['id', 'name', 'value'])
                ->where(['project_id' => $id])
                ->all();

            foreach ($vars as $var) {
                $ret[$var->name] = ['id' => $var->id, 'value' => $var->value];
            }

            Cache::write('variable.Project' . $id, $ret, 'site');
        }

        return (array)$ret;
    }

    /**
     * Convert string to color hash
     *
     * @param string $str Source string
     * @return string
     */
    private function stringToColorCode($str)
    {
        $code = dechex(crc32($str));
        $code = substr($code, 0, 6);

        return $code;
    }

    /**
     * Return thumbnail image for specified project
     *
     * @param \App\Model\Entity\Project $project Project
     * @param int $thumbSize Thumbnail size
     * @return mixed
     */
    public function thumbnail($project, $thumbSize = self::THUMB_SIZE)
    {
        $newImage = null;
        if (empty($project->ico)) {
            $newImage = imagecreatetruecolor($thumbSize, $thumbSize);
            if ($newImage) {
                imagealphablending($newImage, true);
                imagesavealpha($newImage, true);

                $bgRgb = '#' . $this->stringToColorCode($project->title);
                $white = imagecolorallocatealpha(
                    $newImage,
                    (int)hexdec(substr($bgRgb, 1, 2)),
                    (int)hexdec(substr($bgRgb, 3, 2)),
                    (int)hexdec(substr($bgRgb, 5, 2)),
                    0
                );

                $textColor = empty($project->colorize) ? '#ffffff' : $project->colorize;
                $textColor = imagecolorallocatealpha(
                    $newImage,
                    (int)hexdec(substr($textColor, 1, 2)),
                    (int)hexdec(substr($textColor, 3, 2)),
                    (int)hexdec(substr($textColor, 5, 2)),
                    0
                );

                if ($white && $textColor) {
                    imagefill($newImage, 0, 0, $white);

                    $caption = mb_substr($project->title, 0, 1);
                    $parts = explode(' ', $project->title);
                    $caption .= mb_substr($parts[count($parts) - 1], 0, 1);

                    $fontFile = constant('WWW_ROOT') . 'font' . constant('DS') . 'arialbd.ttf';
                    imagettftext(
                        $newImage,
                        (int)($thumbSize * 0.55),
                        0,
                        0,
                        (int)(0.75 * $thumbSize),
                        $textColor,
                        $fontFile,
                        strtoupper($caption)
                    );
                }
            }
        } else {
            $im = imagecreatefromstring(base64_decode($project->ico));
            if ($im) {
                $width = imagesx($im);
                $height = imagesy($im);

                if ($width > $height) {
                    $newHeight = $thumbSize;
                    $newWidth = (int)floor($width * $newHeight / $height);
                    $cropX = (int)ceil(($width - $height) / 2);
                    $cropY = 0;
                } else {
                    $newWidth = $thumbSize;
                    $newHeight = (int)floor($height * $newWidth / $width);
                    $cropX = 0;
                    $cropY = (int)ceil(($height - $width) / 2);
                }

                $newImage = imagecreatetruecolor($thumbSize, $thumbSize);
                if ($newImage) {
                    imagealphablending($newImage, false);
                    imagesavealpha($newImage, true);
                    $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);

                    if ($transparent) {
                        imagefilledrectangle($newImage, 0, 0, $thumbSize, $thumbSize, $transparent);
                        imagecopyresampled(
                            $newImage,
                            $im,
                            0,
                            0,
                            $cropX,
                            $cropY,
                            $newWidth,
                            $newHeight,
                            $width,
                            $height
                        );
                        imagedestroy($im);

                        if (!empty($project->colorize)) {
                            imagefilter(
                                $newImage,
                                IMG_FILTER_COLORIZE,
                                (int)hexdec(substr($project->colorize, 1, 2)),
                                (int)hexdec(substr($project->colorize, 3, 2)),
                                (int)hexdec(substr($project->colorize, 5, 2))
                            );
                        }
                    }
                }
            }
        }

        if ($newImage) {
            $im = $newImage;

            ob_start();
            imagepng($im);
            $imageData = ob_get_contents();
            ob_end_clean();
            imagedestroy($im);

            return $imageData;
        }

        return false;
    }
}
