pipeline { 
    agent any 
    environment {
        BRANCH_NAME = "${GIT_BRANCH.split("/")[1]}"
    }
    stages {
        stage('Test'){
            steps {
               echo "TEST"
            }
        }
        stage('Deploy') {
            steps {
                sh 'git status'
                script{                    
                    echo BRANCH_NAME
                    switch(BRANCH_NAME){
                        case "master":
                            break;
                        case "staging":
                            echo "staging"
                            break;
                        default:                            
                            break;
                        
                    }
                }
            }
        }
    }
}
