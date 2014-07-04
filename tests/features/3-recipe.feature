Feature: As an admin user I can create some recipes
  Scenario: I log in and visit one project
    Given I am logged as an admin user "admin" "admin"
    Given I am on "/"
    Then I should see "Wapistrano Status"
    Given I am on "/recipes/"
    Then I follow "Add new"
    Then I wait for text "Add new recipe" to appear
    Given I fill in "wapistrano_corebundle_recipes_name" with "testRecipe"
    Given I fill in "wapistrano_corebundle_recipes_description" with "A description for testRecipe"
    Given I fill in "wapistrano_corebundle_recipes_body" with
    """
      namespace :uploads do
      task :symlink, :except => { :no_release => true } do
        on_rollback do
           run "cd #{previous_release}; umask 002; #{composer} install -n"
          end
          run "if [ -d '#{release_path}/app/cache' ]; then rm -rf #{release_path}/app/cache; fi; umask 000; mkdir #{release_path}/app/cache;"
          run "rm -rf #{release_path}/app/logs"
          run "ln -nfs #{shared_path}/log #{release_path}/app/logs"

          run "rm -rf #{release_path}/web/uploads"
          run "ln -nfs #{shared_path}/web/uploads #{release_path}/web/uploads"
          run "rm -rf #{release_path}/web/media"
          run "if [ -d #{shared_path}/web/media ]; then ln -nfs #{shared_path}/web/media #{release_path}/web/media; fi"

          run "rm -rf #{release_path}/vendor"
          if exists?(:vendor_path)
            #if !vendor_path.empty?
              run "mkdir -p #{vendor_path}"
              run "ln -nfs #{vendor_path} #{release_path}/vendor"
            #else
            #  run "ln -nfs #{shared_path}/vendor #{release_path}/vendor"
            #end
          else
              run "ln -nfs #{shared_path}/vendor #{release_path}/vendor"
          end
      end
      end
    """
    Then I press "Save"
    Then I wait for text "Recipes list" to appear